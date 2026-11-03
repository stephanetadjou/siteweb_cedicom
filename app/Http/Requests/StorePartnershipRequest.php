<?php

namespace App\Http\Requests;

use App\Models\PartnershipRequest;
use Illuminate\Validation\Rule;

class StorePartnershipRequest extends SiteFormRequest
{
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:150'],
            'country' => ['required', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'sector' => ['required', Rule::in(PartnershipRequest::SECTORS)],
            'message' => ['nullable', 'string', 'max:3000'],
        ] + $this->honeypotRules();
    }

    public function attributes(): array
    {
        return [
            'company' => mb_strtolower(__('form.company')),
            'country' => mb_strtolower(__('form.country')),
            'contact_name' => mb_strtolower(__('form.contact_name')),
            'email' => mb_strtolower(__('form.email_pro')),
            'phone' => mb_strtolower(__('form.phone')),
            'sector' => mb_strtolower(__('form.sector')),
            'message' => mb_strtolower(__('form.project')),
        ];
    }
}
