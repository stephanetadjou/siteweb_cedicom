<?php

namespace App\Http\Controllers\Admin;

use App\Models\PartnershipRequest;
use Illuminate\Database\Eloquent\Model;

class PartnershipRequestController extends InboxController
{
    protected function model(): string
    {
        return PartnershipRequest::class;
    }

    protected function slug(): string
    {
        return 'partenariats';
    }

    protected function labels(): array
    {
        return ['title' => 'Demandes de partenariat', 'singular' => 'Demande de partenariat', 'done' => 'Traitée', 'empty' => 'Aucune demande de partenariat.'];
    }

    protected function statusColumn(): string
    {
        return 'processed_at';
    }

    protected function searchable(): array
    {
        return ['company', 'country', 'contact_name', 'email', 'phone', 'message'];
    }

    protected function row(Model $item): array
    {
        return [
            'title' => $item->company,
            'subtitle' => collect([$item->contact_name, $item->email])->filter()->implode(' · '),
            'meta' => $item->country.' · '.$this->sector($item),
        ];
    }

    protected function details(Model $item): array
    {
        return [
            'Entreprise' => $item->company,
            'Pays' => $item->country,
            'Contact' => $item->contact_name,
            'E-mail' => $item->email,
            'Téléphone' => $item->phone,
            "Secteur d'activité" => $this->sector($item),
            'Message' => $item->message,
            'Langue du site' => $item->locale ? strtoupper($item->locale) : null,
        ];
    }

    private function sector(Model $item): ?string
    {
        return $item->sector ? __('form.sectors.'.$item->sector, [], 'fr') : null;
    }
}
