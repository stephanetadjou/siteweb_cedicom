<x-mail::message>
# Nouvelle demande de partenariat

**Entreprise :** {{ $demande->company }}<br>
**Pays :** {{ $demande->country }}<br>
**Contact :** {{ $demande->contact_name ?: '—' }}<br>
**E-mail :** {{ $demande->email }}<br>
**Téléphone :** {{ $demande->phone ?: '—' }}<br>
**Secteur d'activité :** {{ __('form.sectors.'.$demande->sector, [], 'fr') }}<br>
**Langue du visiteur :** {{ strtoupper($demande->locale) }}<br>
**Reçu le :** {{ $demande->created_at->format('d/m/Y à H:i') }}

@if ($demande->message)
<x-mail::panel>
{{ $demande->message }}
</x-mail::panel>
@endif

Pour répondre, utilisez simplement « Répondre » dans votre messagerie : la réponse part vers le visiteur.

<x-mail::button :url="url('/admin')">
Ouvrir le back office
</x-mail::button>
</x-mail::message>
