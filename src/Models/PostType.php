<?php

namespace Maxi032\LaravelAdminPackage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $type
 * @property string $icon
 * @property bool|int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Post> $posts
 */
class PostType extends Model
{
    use HasFactory, SoftDeletes;

    public const DEFAULT_ICON = 'cil-description';

    public static function iconOptions(): array
    {
        // The list comes from the bundled @coreui/icons Free stylesheet.
        $icons = file(__DIR__.'/../../resources/coreui-free-icons.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return array_combine($icons, array_map(fn (string $icon) => Str::headline(substr($icon, 4)), $icons));
    }

    public function getRouteKeyName(): string
    {
        return 'type';
    }

    protected $fillable = [
        'type',
        'icon',
        'status',
    ];

    public function displayName(): string
    {
        if ($this->type === null) {
            return (string) $this->getKey();
        }

        return Lang::has($this->type) ? __($this->type) : Str::headline($this->type);
    }

    /**
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'type_id');
    }

    public function postOptions(): BelongsToMany
    {
        return $this->belongsToMany(PostOption::class, 'post_type_options');
    }

    public function hasPosts(): bool
    {
        return $this->exists && $this->posts()->withTrashed()->exists();
    }
}
