<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'type_id',
        'status'
    ];

    /**
     * @return HasMany
     */
    public function post(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'type_id');
    }

    /**
     * Get category by type
     *
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->whereHas('type', fn (Builder $query) => $query->where('type', $type));
    }

    public function scopeByTypeId(Builder $query, int $typeId): Builder
    {
        return $query->where('type_id', $typeId);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'category_id');
    }

    /**
     * Get translations by language
     *
     * @param $query
     * @param $language
     * @return mixed
     */
    public function scopeByLanguage($query, $language): mixed
    {
        return $query->where('language', $language);
    }
}
