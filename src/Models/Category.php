<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  \Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * Get category by type
     *
     * @param $query
     * @param string $type
     * @return mixed
     */
    public function scopeByType($query, $type): mixed
    {
        return $query->where('type', $type);
    }

}
