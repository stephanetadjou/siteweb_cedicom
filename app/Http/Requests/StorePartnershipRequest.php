<?php

namespace App\Http\Requests;

use App\Models\PartnershipRequest;
use Illuminate\Validation\Rule;

class StorePartnershipRequest extends SiteFormRequest
{
    protected array $singleLine = ['company', 'country', 'contact_name', 'email', 'phone'];

    public function rules(): array
    {
        return [
            'company' => $this->line(true, 150),
            'country' => $this->line(true, 100),
            'contact_name' => $this->line(false, 150),
            'email' => $this->emailRules(),
            'phone' => $this->phoneRules(),
            'sector' => ['required', Rule::in(PartnershipRequest::SECTORS)],
            'message' => $this->textRules(false, 3000),
        ];
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
