<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Maxi032\LaravelAdminPackage\Models\Category;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'sort_order',
        'status',
        'type_id',
    ];

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
     * @param $category
     * @return mixed
     */
    public function scopeByCategory($query, $category): mixed
    {
        return $query->where('type', $category);
    }

    /**
     * Get all translations of post
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
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
