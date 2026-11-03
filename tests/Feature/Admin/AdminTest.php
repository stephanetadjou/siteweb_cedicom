<?php

use App\Models\ContactMessage;
use App\Models\PartnershipRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

it('redirige un visiteur non connecté vers la connexion', function (string $url) {
    $this->get($url)->assertRedirect('/admin/login');
})->with(['/admin', '/admin/partenariats', '/admin/messages', '/admin/messages/export']);

it('refuse le back office à un compte qui n\'est pas administrateur', function (string $url) {
    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
})->with(['/admin', '/admin/partenariats', '/admin/messages/export']);

it('refuse la connexion d\'un compte non administrateur', function () {
    $user = User::factory()->create(['password' => 'secret-de-test']);

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'secret-de-test'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('connecte un administrateur et affiche le tableau de bord', function () {
    $admin = User::factory()->create(['password' => 'secret-de-test']);
    $admin->forceFill(['is_admin' => true])->save();
    PartnershipRequest::create(['company' => 'Brasserie Test', 'country' => 'Gabon', 'email' => 'a@b.test', 'sector' => 'agro']);

    $this->post('/admin/login', ['email' => $admin->email, 'password' => 'secret-de-test'])->assertRedirect('/admin');

    $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Dashboard')
        ->where('stats.partnerships.new', 1)
        ->where('stats.messages.new', 0)
        ->has('latestPartnerships', 1));
});

it('n\'a pas d\'inscription publique', function () {
    $this->get('/register')->assertNotFound();
});

it('liste, recherche, filtre et change le statut d\'une demande', function () {
    $done = PartnershipRequest::create(['company' => 'Ancienne SA', 'country' => 'Gabon', 'email' => 'x@y.test', 'sector' => 'fmcg']);
    $done->forceFill(['processed_at' => now()])->save();
    $new = PartnershipRequest::create(['company' => 'Nouvelle SARL', 'country' => 'Congo', 'email' => 'n@y.test', 'sector' => 'agro']);

    $this->actingAs($this->admin)->get('/admin/partenariats?status=new')
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Inbox/Index')->has('items.data', 1)->where('items.data.0.title', 'Nouvelle SARL'));

    $this->get('/admin/partenariats?q=Ancienne')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.done', true));

    $this->get("/admin/partenariats/{$new->id}")
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Inbox/Show')->where('item.title', 'Nouvelle SARL'));

    $this->patch("/admin/partenariats/{$new->id}/statut")->assertRedirect();
    expect($new->fresh()->processed_at)->not->toBeNull();

    $this->patch("/admin/partenariats/{$new->id}/statut");
    expect($new->fresh()->processed_at)->toBeNull();
});

it('marque un message comme traité', function () {
    $message = ContactMessage::create(['name' => 'Awa Test', 'email' => 'awa@exemple.test', 'subject' => 'sales', 'message' => 'Bonjour']);

    $this->actingAs($this->admin)->patch("/admin/messages/{$message->id}/statut")->assertRedirect();

    expect($message->fresh()->read_at)->not->toBeNull();
});

it('exporte les messages en CSV avec les filtres', function () {
    ContactMessage::create(['name' => 'Awa Test', 'email' => 'awa@exemple.test', 'subject' => 'sales', 'message' => 'Bonjour']);
    ContactMessage::create(['name' => 'Paul Autre', 'email' => 'paul@exemple.test', 'subject' => 'other', 'message' => 'Salut']);

    $response = $this->actingAs($this->admin)->get('/admin/messages/export?q=Awa');

    $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())
        ->toContain('Awa Test')
        ->toContain('Information commerciale')
        ->not->toContain('Paul Autre');
});

it('crée un compte administrateur en ligne de commande', function () {
    $this->artisan('cedicom:admin', ['email' => 'Direction@Exemple.test'])->assertSuccessful();

    expect(User::where('email', 'direction@exemple.test')->sole()->is_admin)->toBeTrue();
});
