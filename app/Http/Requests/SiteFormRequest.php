<?php

namespace App\Http\Requests;

use App\Rules\NoSpamContent;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base des formulaires publics : messages traduits (lang/{fr,en}.json) et
 * validation stricte anti-spam. Le champ piège et le jeton horodaté sont
 * vérifiés avant, par le middleware ProtectPublicForm (rejet silencieux).
 */
abstract class SiteFormRequest extends FormRequest
{
    /** Champs d'une seule ligne : espaces multiples ramenés à un seul. */
    protected array $singleLine = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach ($this->singleLine as $field) {
            if (is_string($value = $this->input($field))) {
                // Les retours à la ligne sont gardés ici pour être refusés par la règle « no_newline ».
                $clean[$field] = trim(preg_replace('/[ \t]+/u', ' ', $value));
            }
        }
        $this->merge($clean);
    }

    /** Une seule ligne, sans lien ni HTML (noms, entreprise, pays…). */
    protected function line(bool $required, int $max): array
    {
        return [$required ? 'required' : 'nullable', 'string', "max:$max", 'not_regex:/[\r\n]/', new NoSpamContent(0)];
    }

    protected function emailRules(): array
    {
        return ['required', 'string', 'max:150', 'not_regex:/[\r\n]/', config('antispam.email_dns') ? 'email:rfc,dns' : 'email:rfc'];
    }

    protected function phoneRules(): array
    {
        return ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\/ -]{6,40}$/'];
    }

    protected function textRules(bool $required, int $max): array
    {
        return [$required ? 'required' : 'nullable', 'string', "max:$max", new NoSpamContent(2)];
    }

    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'email' => __('validation.email'),
            'max' => __('validation.max'),
            'in' => __('validation.in'),
            'not_regex' => __('validation.single_line'),
            'phone.regex' => __('validation.phone'),
        ];
    }
}
