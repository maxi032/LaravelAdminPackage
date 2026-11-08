<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostOptionValue extends Model
{
    protected $fillable = ['locale', 'post_option_id', 'boolean_value', 'integer_value', 'text_value'];

    protected $casts = [
        'boolean_value' => 'boolean',
        'integer_value' => 'integer',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(PostOption::class, 'post_option_id');
    }

    public function scopeTrueFor(Builder $query, string $key): Builder
    {
        return $query->where('locale', '')
            ->where('boolean_value', true)
            ->whereHas('definition', fn (Builder $definition) => $definition->editableOnPosts()->where('key', $key));
    }
}
