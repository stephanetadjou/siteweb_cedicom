<?php

use App\Models\ContactMessage;
use App\Models\PartnershipRequest;
use Database\Seeders\SiteContentSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => $this->seed(SiteContentSeeder::class));

it('affiche l\'accueil en français par défaut avec les contenus de la base', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Site/Home')
            ->where('locale', 'fr')
            ->has('poles', 3)
            ->where('poles.0.title', 'FMCG & produits essentiels')
            ->has('brands', 30)
            ->has('divisions', 2)
            ->has('divisions.1.franchises', 6)
            ->has('divisions.1.concepts', 5)
            ->where('translations', fn ($t) => $t['nav.home'] === 'Accueil'));
});

it('bascule en anglais et traduit les contenus de la base', function () {
    $this->from('/contact')->get('/langue/en')->assertRedirect('/contact');

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'en')
            ->where('poles.1.title', 'Fashion & Lifestyle')
            ->where('divisions.0.categories.0', 'Fashion franchises')
            ->where('translations', fn ($t) => $t['nav.home'] === 'Home'));
});

it('refuse une langue inconnue', function () {
    $this->get('/langue/de')->assertNotFound();
});

it('affiche les pages contact et partenaire', function (string $url, string $component) {
    $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['/contact', 'Site/Contact'],
    ['/devenir-partenaire', 'Site/Partner'],
]);

it('enregistre une demande de partenariat', function () {
    $this->from('/devenir-partenaire')
        ->post('/devenir-partenaire', [
            'company' => 'Brasserie Test',
            'country' => 'Cameroun',
            'email' => 'contact@brasserie.test',
            'sector' => 'agro',
        ])
        ->assertRedirect('/devenir-partenaire')
        ->assertSessionHas('success');

    expect(PartnershipRequest::sole())
        ->company->toBe('Brasserie Test')
        ->sector->toBe('agro')
        ->locale->toBe('fr');
});

it('valide la demande de partenariat avec des messages traduits', function (string $locale, string $expected) {
    $this->withSession(['locale' => $locale])
        ->post('/devenir-partenaire', ['email' => 'pas-un-email', 'sector' => 'inconnu'])
        ->assertSessionHasErrors(['company' => $expected, 'email', 'sector']);

    expect(PartnershipRequest::count())->toBe(0);
})->with([
    ['fr', 'Le champ société est obligatoire.'],
    ['en', 'The company field is required.'],
]);

it('enregistre un message de contact en anglais', function () {
    $this->withSession(['locale' => 'en'])
        ->post('/contact', [
            'name' => 'Awa',
            'email' => 'awa@example.com',
            'subject' => 'delivery',
            'message' => 'Hello',
        ])
        ->assertSessionHas('success', __('form.contact_success', [], 'en'));

    expect(ContactMessage::sole()->locale)->toBe('en');
});

it('bloque les envois qui remplissent le champ piège', function () {
    $this->post('/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'subject' => 'other',
        'message' => 'spam',
        'website' => 'http://spam.test',
    ])->assertSessionHasErrors('website');

    expect(ContactMessage::count())->toBe(0);
});

it('a les mêmes clés de traduction en français et en anglais', function () {
    $fr = array_keys(json_decode(file_get_contents(lang_path('fr.json')), true));
    $en = array_keys(json_decode(file_get_contents(lang_path('en.json')), true));

    expect(array_diff($fr, $en))->toBeEmpty()
        ->and(array_diff($en, $fr))->toBeEmpty();
});
