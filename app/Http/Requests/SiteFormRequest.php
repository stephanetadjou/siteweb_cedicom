<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base des formulaires publics : messages traduits (lang/{fr,en}.json)
 * et refus des envois qui remplissent le champ piège « website » (anti-spam).
 */
abstract class SiteFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function honeypotRules(): array
    {
        return [
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'email' => __('validation.email'),
            'max' => __('validation.max'),
            'in' => __('validation.in'),
            'website.prohibited' => __('validation.blocked'),
        ];
    }
}
