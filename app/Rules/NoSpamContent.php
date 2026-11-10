<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Refuse les balises HTML et les textes contenant plus de $maxLinks liens. */
class NoSpamContent implements ValidationRule
{
    public function __construct(private int $maxLinks = 0) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $text = (string) $value;
        $links = preg_match_all('~(https?://|www\.)~i', $text);

        if (preg_match('~<\s*/?\s*[a-z!][^>]*>~i', $text) || $links > $this->maxLinks) {
            $fail(__('validation.spam_content'));
        }
    }
}
