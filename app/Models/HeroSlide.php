<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    use HasTranslations;

    protected array $translatable = ['alt'];

    protected $fillable = ['image', 'alt', 'position', 'veil', 'sort', 'visible'];

    protected function casts(): array
    {
        return ['visible' => 'boolean'];
    }

    public function toLocalizedArray(): array
    {
        return [
            'id' => $this->id,
            'src' => asset($this->image),
            'alt' => $this->translate('alt') ?? '',
            'position' => $this->position,
            'veil' => $this->veil,
        ];
    }
}
