<?php

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Models\PartnershipRequest;
use App\Models\User;
use App\Support\SpamGuard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

$contact = fn (array $override = []) => $override + [
    'name' => 'Awa Test',
    'email' => 'awa@exemple.test',
    'subject' => 'sales',
    'message' => 'Bonjour, je souhaite un devis.',
];

function enableRecaptcha(): void
{
    config(['antispam.recaptcha.site_key' => 'cle-site-test', 'antispam.recaptcha.secret_key' => 'cle-secrete-test']);
}

function fakeRecaptcha(float $score, string $action = 'contact', bool $success = true): void
{
    Http::fake(['www.google.com/recaptcha/*' => Http::response(compact('success', 'score', 'action'))]);
}

beforeEach(fn () => Mail::fake());

describe('rejet silencieux', function () use ($contact) {
    it('rejette sans rien enregistrer ni envoyer', function (array $fields) use ($contact) {
        $this->from('/contact')->post('/contact', $contact($fields))
            ->assertRedirect('/contact')
            ->assertSessionHas('success', __('form.contact_success'))
            ->assertSessionHasNoErrors();

        expect(ContactMessage::count())->toBe(0);
        Mail::assertNothingSent();
    })->with([
        'champ piège rempli' => fn () => ['website' => 'http://spam.test'] + antispam(),
        'sans jeton' => fn () => [],
        'jeton falsifié' => fn () => ['_t' => 'faux'],
        'envoyé en moins de 3 secondes' => fn () => antispam(1),
        'jeton de plus de 24 h' => fn () => antispam(60 * 60 * 25),
    ]);

    it('accepte un envoi humain', function () use ($contact) {
        $this->post('/contact', $contact(antispam()))->assertSessionHas('success');

        expect(ContactMessage::count())->toBe(1);
    });
});

describe('limitation de débit', function () use ($contact) {
    it('accepte 3 envois par 10 minutes et par formulaire, puis refuse', function () use ($contact) {
        foreach (range(1, 3) as $i) {
            $this->post('/contact', $contact(antispam()))->assertSessionHasNoErrors();
        }

        $this->post('/contact', $contact(antispam()))->assertSessionHasErrors(['form' => __('form.too_many')]);
        expect(ContactMessage::count())->toBe(3);

        // L'autre formulaire a son propre compteur.
        $this->post('/devenir-partenaire', ['company' => 'SA', 'country' => 'Gabon', 'email' => 'a@b.test', 'sector' => 'agro'] + antispam())
            ->assertSessionHasNoErrors();
    });

    it('limite les tentatives de connexion admin à 5 par minute', function () {
        foreach (range(1, 5) as $i) {
            $this->post('/admin/login', ['email' => 'x@y.test', 'password' => 'faux']);
        }

        $this->post('/admin/login', ['email' => 'x@y.test', 'password' => 'faux'])->assertTooManyRequests();
    });
});

describe('validation stricte', function () use ($contact) {
    it('refuse les contenus suspects', function (array $fields, string $field) use ($contact) {
        $this->post('/contact', $contact($fields) + antispam())->assertSessionHasErrors($field);

        expect(ContactMessage::count())->toBe(0);
    })->with([
        'retour à la ligne dans le nom' => [['name' => "Awa\nBcc: victime@exemple.test"], 'name'],
        'injection d\'en-tête dans l\'e-mail' => [['email' => "awa@exemple.test\nBcc: x@y.test"], 'email'],
        'lien dans le nom' => [['name' => 'Promo https://spam.test'], 'name'],
        'balise HTML dans le message' => [['message' => 'Bonjour <a href="https://spam.test">clic</a>'], 'message'],
        'plus de 2 liens dans le message' => [['message' => 'https://a.test https://b.test www.c.test'], 'message'],
        'message trop long' => [['message' => str_repeat('a', 5001)], 'message'],
        'téléphone fantaisiste' => [['phone' => 'appelez-moi'], 'phone'],
    ]);

    it('accepte 2 liens dans le message et nettoie les espaces', function () use ($contact) {
        $this->post('/contact', $contact([
            'name' => '  Awa    Test ',
            'message' => 'Voir https://a.test et https://b.test',
        ]) + antispam())->assertSessionHasNoErrors();

        expect(ContactMessage::sole()->name)->toBe('Awa Test');
    });
});

describe('reCAPTCHA v3', function () use ($contact) {
    it('expose seulement la clé publique au navigateur', function () {
        enableRecaptcha();

        $this->get('/contact')
            ->assertInertia(fn (Assert $page) => $page->where('antispam.recaptchaKey', 'cle-site-test')->has('antispam.token'))
            ->assertDontSee('cle-secrete-test');
    });

    it('est désactivé sans clés', function () {
        $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('antispam.recaptchaKey', null));
    });

    it('accepte un bon score pour la bonne action', function () use ($contact) {
        enableRecaptcha();
        fakeRecaptcha(0.9);

        $this->post('/contact', $contact(antispam() + ['recaptcha_token' => 'jeton']))->assertSessionHas('success');

        expect(ContactMessage::count())->toBe(1);
        Http::assertSent(fn ($request) => $request['secret'] === 'cle-secrete-test' && $request['response'] === 'jeton');
    });

    it('rejette en silence un mauvais résultat', function (callable $setup, array $fields) use ($contact) {
        enableRecaptcha();
        $setup();

        $this->post('/contact', $contact(antispam() + $fields))->assertSessionHas('success');

        expect(ContactMessage::count())->toBe(0);
    })->with([
        'score trop bas' => [fn () => fakeRecaptcha(0.2), ['recaptcha_token' => 'jeton']],
        'autre action' => [fn () => fakeRecaptcha(0.9, 'login'), ['recaptcha_token' => 'jeton']],
        'jeton refusé par Google' => [fn () => fakeRecaptcha(0.9, 'contact', false), ['recaptcha_token' => 'jeton']],
        'jeton absent' => [fn () => fakeRecaptcha(0.9), []],
    ]);

    it('laisse passer si Google est injoignable', function () use ($contact) {
        enableRecaptcha();
        // Vraie connexion refusée (les fausses erreurs de connexion d'Http::fake font planter PHP sous Windows).
        config(['antispam.recaptcha.verify_url' => 'http://127.0.0.1:1/siteverify']);

        $this->post('/contact', $contact(antispam() + ['recaptcha_token' => 'jeton']))->assertSessionHas('success');

        expect(ContactMessage::count())->toBe(1);
    });

    it('protège la connexion admin', function () {
        enableRecaptcha();
        fakeRecaptcha(0.1, 'login');
        $admin = User::factory()->create(['password' => 'secret-de-test']);
        $admin->forceFill(['is_admin' => true])->save();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'secret-de-test', 'recaptcha_token' => 'jeton'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    });
});

describe('e-mails', function () use ($contact) {
    it('n\'écrit jamais à l\'adresse saisie par le visiteur', function () use ($contact) {
        $this->post('/contact', $contact(antispam()));

        Mail::assertSent(NewContactMessage::class, fn (NewContactMessage $mail) => $mail->hasTo('info@cedicomgroup.com')
            && ! $mail->hasTo('awa@exemple.test')
            && $mail->hasReplyTo('awa@exemple.test'));
        Mail::assertSentCount(1);
    });

    it('plafonne les alertes par heure mais garde les demandes', function () use ($contact) {
        config(['antispam.alerts_per_hour' => 1]);

        $this->post('/contact', $contact(antispam()));
        $this->post('/devenir-partenaire', ['company' => 'SA', 'country' => 'Gabon', 'email' => 'a@b.test', 'sector' => 'agro'] + antispam());

        Mail::assertSentCount(1);
        expect(ContactMessage::count() + PartnershipRequest::count())->toBe(2);
    });
});

it('génère des jetons signés que le navigateur ne peut pas forger', function () {
    expect(app(SpamGuard::class)->token())->not->toBe((string) time());
});
