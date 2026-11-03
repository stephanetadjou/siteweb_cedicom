<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasTranslations;

    protected array $translatable = ['title', 'description', 'image_alt', 'categories'];

    protected $fillable = ['slug', 'title', 'description', 'image', 'image_alt', 'gallery', 'categories', 'sort'];

    protected $casts = ['gallery' => 'array'];

    public function enseignes(): HasMany
    {
        return $this->hasMany(Enseigne::class)->orderBy('sort');
    }

    public function toLocalizedArray(): array
    {
        $enseignes = fn (string $type) => $this->enseignes
            ->where('type', $type)
            ->map(fn (Enseigne $e) => ['id' => $e->id, 'name' => $e->name, 'logo' => asset($e->logo)])
            ->values()
            ->all();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title'),
            'description' => $this->translate('description'),
            'image' => asset($this->image),
            'imageAlt' => $this->translate('image_alt'),
            'gallery' => collect($this->gallery ?? [])
                ->map(fn (array $photo) => [
                    'image' => asset($photo['image']),
                    'alt' => $photo['alt'][app()->getLocale()] ?? $photo['alt'][config('app.fallback_locale')] ?? $photo['alt']['fr'] ?? '',
                ])
                ->all(),
            'categories' => $this->translate('categories') ?? [],
            'franchises' => $enseignes('franchise'),
            'concepts' => $enseignes('concept'),
        ];
    }
}
