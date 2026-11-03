<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends SiteFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', Rule::in(ContactMessage::SUBJECTS)],
            'message' => ['required', 'string', 'max:5000'],
        ] + $this->honeypotRules();
    }

    public function attributes(): array
    {
        return [
            'name' => mb_strtolower(__('form.name')),
            'email' => mb_strtolower(__('form.email')),
            'phone' => mb_strtolower(__('form.phone')),
            'subject' => mb_strtolower(__('form.subject')),
            'message' => mb_strtolower(__('form.message')),
        ];
    }
}
