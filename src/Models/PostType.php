<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Relations\HasMany;

class PostType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
}
