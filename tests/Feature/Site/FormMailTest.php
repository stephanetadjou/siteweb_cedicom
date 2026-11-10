<?php

use App\Mail\NewContactMessage;
use App\Mail\NewPartnershipRequest;
use App\Models\ContactMessage;
use App\Models\PartnershipRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

$contactForm = [
    'name' => 'Awa Test',
    'email' => 'awa@exemple.test',
    'phone' => '+241 01 02 03 04',
    'subject' => 'sales',
    'message' => 'Bonjour, je souhaite un devis.',
];

$partnerForm = [
    'company' => 'Brasserie Test',
    'country' => 'Cameroun',
    'contact_name' => 'Jean Test',
    'email' => 'jean@brasserie.test',
    'sector' => 'agro',
    'message' => 'Proposition de distribution.',
];

it('envoie un message de contact à info@cedicomgroup.com avec Reply-To sur le visiteur', function () use ($contactForm) {
    Mail::fake();

    $this->post('/contact', $contactForm + antispam())->assertSessionHas('success');

    Mail::assertSent(NewContactMessage::class, fn (NewContactMessage $mail) => $mail->hasTo('info@cedicomgroup.com')
        && $mail->hasReplyTo('awa@exemple.test')
        && $mail->contact->is(ContactMessage::sole()));
});

it('envoie une demande de partenariat à info@cedicomgroup.com avec Reply-To sur le visiteur', function () use ($partnerForm) {
    Mail::fake();

    $this->post('/devenir-partenaire', $partnerForm + antispam())->assertSessionHas('success');

    Mail::assertSent(NewPartnershipRequest::class, fn (NewPartnershipRequest $mail) => $mail->hasTo('info@cedicomgroup.com')
        && $mail->hasReplyTo('jean@brasserie.test'));
});

it('envoie à plusieurs destinataires si MAIL_NOTIFY_TO en liste plusieurs', function () use ($contactForm) {
    config(['mail.notify_to' => ['direction@cedicom.test', 'ventes@cedicom.test']]);
    Mail::fake();

    $this->post('/contact', $contactForm + antispam());

    Mail::assertSent(NewContactMessage::class, fn (NewContactMessage $mail) => $mail->hasTo('direction@cedicom.test')
        && $mail->hasTo('ventes@cedicom.test'));
});

it('enregistre le formulaire même si l\'envoi de l\'e-mail échoue', function (string $url, array $form, string $model) {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP indisponible'));
    Log::shouldReceive('error')->once();

    $this->post($url, $form + antispam())->assertSessionHas('success');

    expect($model::count())->toBe(1);
})->with([
    'contact' => ['/contact', $contactForm, ContactMessage::class],
    'partenariat' => ['/devenir-partenaire', $partnerForm, PartnershipRequest::class],
]);

it('affiche tous les champs dans les e-mails', function () use ($contactForm, $partnerForm) {
    (new NewContactMessage(ContactMessage::create($contactForm + ['locale' => 'fr'])))
        ->assertSeeInHtml('Awa Test')
        ->assertSeeInHtml('+241 01 02 03 04')
        ->assertSeeInHtml('Information commerciale')
        ->assertSeeInHtml('Bonjour, je souhaite un devis.')
        ->assertSeeInHtml('/admin');

    (new NewPartnershipRequest(PartnershipRequest::create($partnerForm + ['locale' => 'en'])))
        ->assertSeeInHtml('Brasserie Test')
        ->assertSeeInHtml('Cameroun')
        ->assertSeeInHtml('Proposition de distribution.')
        ->assertSeeInHtml('EN');
});
