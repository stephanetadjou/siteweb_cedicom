<x-mail::message>
# Nouveau message de contact

**Nom :** {{ $contact->name }}<br>
**E-mail :** {{ $contact->email }}<br>
**Téléphone :** {{ $contact->phone ?: '—' }}<br>
**Objet :** {{ __('form.subjects.'.$contact->subject, [], 'fr') }}<br>
**Langue du visiteur :** {{ strtoupper($contact->locale) }}<br>
**Reçu le :** {{ $contact->created_at->format('d/m/Y à H:i') }}

<x-mail::panel>
{{ $contact->message }}
</x-mail::panel>

Pour répondre, utilisez simplement « Répondre » dans votre messagerie : la réponse part vers le visiteur.

<x-mail::button :url="url('/admin')">
Ouvrir le back office
</x-mail::button>
</x-mail::message>
