<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Maxi032\LaravelAdminPackage\Enums\PostStatusEnum;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'sort_order',
        'status',
        'type_id',
        'category_id',
    ];

    //protected $appends = ['status_badge'];

    public function statusbadge(): Attribute
    {
        return Attribute::make(get: fn() => PostStatusEnum::badgeClass($this->status));
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PostType::class);
    }

    /**
     * Get posts by status
     *
     * @param $query
     * @param $status
     * @return mixed
     */
    public function scopeStatus($query, $status): mixed
    {
        return $query->where('status', $status);
    }

    /**
     * Get posts by category
     *
     * @param $query
     * @param int $categoryId
     * @return mixed
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->whereHas('type', fn(Builder $query) => $query->where('type', $type));
    }

    /** Return the saved option values associated with this post. */
    public function optionValues(): HasMany
    {
        return $this->hasMany(PostOptionValue::class);
    }

    /** Resolve an enabled option by key using its saved value, locale fallback, or default. */
    public function option(string $key, ?string $locale = null): bool|int|string|null
    {
        $definition = PostOption::enabled()->where('key', $key)->first();
        if ($definition === null) {
            return null;
        }

        if (!$definition->visible_in_post) {
            return $definition->defaultValue();
        }

        $locales = $definition->is_translatable
            ? [$locale ?? app()->getLocale(), config('app.fallback_locale')]
            : [''];
        $values = $this->optionValues->where('post_option_id', $definition->id);
        foreach (array_unique($locales) as $language) {
            if ($language !== null && ($value = $values->firstWhere('locale', $language)) !== null) {
                return $value->getAttribute($definition->valueColumn());
            }
        }

        return $definition->defaultValue();
    }

    /** Filter posts whose effective featured option is true. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where($this->sharedBooleanOption('featured'), '=', true);
    }

    /** List posts in a category with sticky posts first, then by sort order and ID. */
    public function scopeInCategoryListing(Builder $query, int $categoryId): Builder
    {
        return $query->byCategory($categoryId)
            ->orderByDesc($this->sharedBooleanOption('sticky_in_category'))
            ->orderBy($this->qualifyColumn('sort_order'))
            ->orderBy($this->qualifyColumn('id'));
    }

    /** Build a subquery for the effective value of an enabled, shared Boolean option. */
    private function sharedBooleanOption(string $key): Builder
    {
        $value = PostOptionValue::query()->select('boolean_value')
            ->whereColumn('post_id', $this->qualifyColumn('id'))
            ->whereColumn('post_option_id', 'post_option.id')
            ->where('post_option.visible_in_post', true)
            ->where('locale', '');

        return PostOption::enabled()->where('key', $key)->where('type', 'boolean')->where('is_translatable', false)
            ->selectRaw('COALESCE(('.$value->toSql().'), boolean_default)', $value->getBindings());
    }

    /**
     * Get all translations of post
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class, 'post_id');
    }

    /**
     * Filter posts that have a non-deleted translation in the given language.
     */
    public function scopeByLanguage(Builder $query, string $language): Builder
    {
        return $query->whereHas('translations',
            fn(Builder $translations) => $translations->where('language', $language));
    }

}
