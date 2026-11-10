<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends SiteFormRequest
{
    protected array $singleLine = ['name', 'email', 'phone'];

    public function rules(): array
    {
        return [
            'name' => $this->line(true, 150),
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(),
            'subject' => ['required', Rule::in(ContactMessage::SUBJECTS)],
            'message' => $this->textRules(true, 5000),
        ];
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
