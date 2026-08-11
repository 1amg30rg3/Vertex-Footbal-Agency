<?php

namespace App\Models;

use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class TeamMember extends Model
{
    use HasFactory;
    use HasMedia;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published'];

    protected $guarded = ['id'];

    /** @var list<string> */
    public array $translatable = ['name', 'role', 'bio'];

    protected array $slugSource = ['name'];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.str_replace('%', '\%', $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('name', 'like', $like)
            ->orWhere('role', 'like', $like));
    }
}
