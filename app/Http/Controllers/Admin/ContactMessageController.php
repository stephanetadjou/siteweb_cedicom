<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Model;

class ContactMessageController extends InboxController
{
    protected function model(): string
    {
        return ContactMessage::class;
    }

    protected function slug(): string
    {
        return 'messages';
    }

    protected function labels(): array
    {
        return ['title' => 'Messages de contact', 'singular' => 'Message de contact', 'done' => 'Traité', 'empty' => 'Aucun message.'];
    }

    protected function statusColumn(): string
    {
        return 'read_at';
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'phone', 'message'];
    }

    protected function row(Model $item): array
    {
        return [
            'title' => $item->name,
            'subtitle' => $item->email,
            'meta' => $this->subject($item),
        ];
    }

    protected function details(Model $item): array
    {
        return [
            'Nom' => $item->name,
            'E-mail' => $item->email,
            'Téléphone' => $item->phone,
            'Objet' => $this->subject($item),
            'Message' => $item->message,
            'Langue du site' => $item->locale ? strtoupper($item->locale) : null,
        ];
    }

    private function subject(Model $item): ?string
    {
        return $item->subject ? __('form.subjects.'.$item->subject, [], 'fr') : null;
    }
}
