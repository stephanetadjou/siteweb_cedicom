<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    use HasTranslations;

    protected array $translatable = ['title', 'description', 'tags'];

    protected $fillable = ['title', 'description', 'image', 'image_position', 'logos', 'round_logos', 'tags', 'anchor', 'sort'];

    protected function casts(): array
    {
        return [
            'logos' => 'array',
            'round_logos' => 'boolean',
        ];
    }

    public function toLocalizedArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->translate('title'),
            'description' => $this->translate('description'),
            'tags' => $this->translate('tags'),
            'image' => asset($this->image),
            'imagePosition' => $this->image_position,
            'logos' => collect($this->logos)->map(fn ($l) => ['name' => $l[0], 'src' => asset($l[1])])->all(),
            'roundLogos' => $this->round_logos,
            'anchor' => $this->anchor,
        ];
    }
}
