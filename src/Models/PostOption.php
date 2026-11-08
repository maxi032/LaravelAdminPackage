<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostOption extends Model
{
    use SoftDeletes;

    public const TYPES = ['boolean', 'integer', 'text'];

    protected $table = 'post_option';

    protected $fillable = [
        'key', 'label', 'type', 'is_translatable',
        'enabled', 'sort_order', 'boolean_default', 'visible_in_post', 'is_eager_loaded',
    ];

    protected $attributes = ['visible_in_post' => false, 'is_eager_loaded' => false];

    protected $casts = [
        'is_translatable' => 'boolean',
        'enabled' => 'boolean',
        'boolean_default' => 'boolean',
        'visible_in_post' => 'boolean',
        'is_eager_loaded' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Filter to enabled post options. */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /** Filter to enabled options that editors can set on individual posts. */
    public function scopeEditableOnPosts(Builder $query): Builder
    {
        return $query->enabled()->where('visible_in_post', true);
    }

    /** Filter to enabled options marked for eager loading. */
    public function scopeEagerOnPosts(Builder $query): Builder
    {
        return $query->enabled()->where('is_eager_loaded', true);
    }

    /** Sort options by their configured order, using ID to break ties. */
    public function scopeInDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Get the per-post values saved for this option. */
    public function values(): HasMany
    {
        return $this->hasMany(PostOptionValue::class);
    }

    /** Get the storage column for this option’s value type. */
    public function valueColumn(): string
    {
        return $this->type.'_value';
    }

    /** Get the configured Boolean default or null for other types. */
    public function defaultValue(): ?bool
    {
        return $this->type === 'boolean' ? (bool) $this->boolean_default : null;
    }
}
