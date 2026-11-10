<?php

use App\Models\Division;
use App\Models\Enseigne;
use App\Models\HeroSlide;
use App\Models\PartnerBrand;
use App\Models\Pole;
use App\Models\User;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteContentSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->forceFill(['is_admin' => true])->save();
});

it('réserve la gestion des contenus aux administrateurs', function (string $url) {
    $this->get($url)->assertRedirect('/admin/login');
    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
    $this->actingAs($this->admin)->get($url)->assertOk();
})->with(['/admin/parametres', '/admin/contenus/diaporama', '/admin/contenus/poles', '/admin/contenus/divisions', '/admin/contenus/enseignes', '/admin/contenus/marques', '/admin/contenus/marques/ajouter']);

it('affiche les textes par défaut tant que les paramètres sont vides', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.hero_title', __('hero.title', [], 'fr'))
        ->where('site.phone', '(+241) 01 77 57 48 / 49')
        ->where('site.stat_warehouses', 30000));
});

it('modifie les paramètres du site et les affiche aussitôt sur le site', function () {
    $this->get('/'); // remplit le cache

    $this->actingAs($this->admin)->put('/admin/parametres', [
        'phone' => '(+241) 11 22 33 44',
        'phone_link' => '+24111223344',
        'email' => 'contact@cedicom-group.com',
        'address' => ['fr' => 'Owendo, Gabon', 'en' => 'Owendo, Gabon'],
        'facebook' => 'https://facebook.com/cedicom',
        'instagram' => '',
        'linkedin' => '',
        'whatsapp' => '',
        'stat_warehouses' => 32000,
        'stat_trucks' => 40,
        'stat_years' => 35,
        'hero_title' => ['fr' => 'Nouveau titre', 'en' => 'New title'],
        'hero_lead' => ['fr' => 'Nouvelle intro', 'en' => ''],
    ])->assertSessionHas('success');

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.hero_title', 'Nouveau titre')
        ->where('site.phone', '(+241) 11 22 33 44')
        ->where('site.facebook', 'https://facebook.com/cedicom')
        ->where('site.stat_trucks', 40));

    // Anglais vide : repli sur le texte anglais par défaut.
    $this->get('/langue/en');
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->where('site.hero_title', 'New title')
        ->where('site.hero_lead', __('hero.lead', [], 'en')));
});

it('valide les paramètres', function () {
    $this->actingAs($this->admin)->put('/admin/parametres', ['email' => 'pas-un-email', 'facebook' => 'javascript:alert(1)', 'stat_trucks' => -3])
        ->assertSessionHasErrors(['email', 'facebook', 'stat_trucks', 'hero_title.fr']);
});

it('ajoute une diapositive avec une image redimensionnée et l\'affiche sur l\'accueil', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post('/admin/contenus/diaporama', [
        'image' => UploadedFile::fake()->image('quai.jpg', 3000, 1500),
        'alt' => ['fr' => 'Quai de chargement', 'en' => 'Loading dock'],
        'veil' => 'strong',
        'position' => '',
        'visible' => '1',
    ])->assertRedirect('/admin/contenus/diaporama');

    $slide = HeroSlide::latest('id')->first();
    expect($slide)->veil->toBe('strong')->position->toBe('50% 50%')->sort->toBe(3)
        ->and($slide->image)->toStartWith('storage/uploads/diaporama/');

    $stored = Storage::disk('public')->path(str($slide->image)->after('storage/'));
    expect(getimagesize($stored)[0])->toBe(1920);

    $this->get('/')->assertInertia(fn (Assert $page) => $page->has('heroSlides', 4)->where('heroSlides.3.alt', 'Quai de chargement'));
});

it('refuse un fichier qui n\'est pas une image', function () {
    $this->actingAs($this->admin)->post('/admin/contenus/marques', [
        'name' => 'Test', 'category' => 'food', 'logo' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('logo');
});

it('met en avant et masque des marques', function () {
    $this->get('/');
    $coca = PartnerBrand::where('name', 'Coca-Cola')->sole();
    $barilla = PartnerBrand::where('name', 'Barilla')->sole();

    $this->actingAs($this->admin)->put("/admin/contenus/marques/{$coca->id}", [
        'name' => 'Coca-Cola', 'category' => 'food', 'featured' => '1', 'visible' => '1',
    ])->assertRedirect('/admin/contenus/marques');
    $this->put("/admin/contenus/marques/{$barilla->id}", ['name' => 'Barilla', 'category' => 'food', 'featured' => '0', 'visible' => '0']);

    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('brands', 29)
        ->where('brands', fn ($brands) => collect($brands)->take(3)->pluck('name')->sort()->values()->all() === ['Coca-Cola', 'Danone Nutricia', 'FrieslandCampina']
            && ! collect($brands)->contains('name', 'Barilla')));
});

it('modifie un pôle et vide le cache de l\'accueil', function () {
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('poles.0.title', 'FMCG & produits essentiels'));
    $pole = Pole::orderBy('sort')->first();

    $this->actingAs($this->admin)->put("/admin/contenus/poles/{$pole->id}", [
        'title' => ['fr' => 'Produits de grande consommation', 'en' => 'Consumer goods'],
        'description' => ['fr' => 'Description', 'en' => ''],
        'tags' => ['fr' => '', 'en' => ''],
        'image_position' => 'center',
        'anchor' => 'marques',
    ])->assertSessionHasNoErrors();

    expect($pole->fresh()->image)->toBe('images/poles/fmcg-logos.jpg'); // image gardée sans nouvel envoi
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('poles.0.title', 'Produits de grande consommation'));
});

it('change l\'ordre avec monter et descendre', function () {
    [$first, $second] = Pole::orderBy('sort')->take(2)->get();

    $this->actingAs($this->admin)->patch("/admin/contenus/poles/{$second->id}/monter")->assertRedirect();

    expect(Pole::orderBy('sort')->first()->id)->toBe($second->id);

    $this->patch("/admin/contenus/poles/{$second->id}/descendre");
    expect(Pole::orderBy('sort')->first()->id)->toBe($first->id);
});

it('crée une division avec un slug unique et une enseigne rattachée', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post('/admin/contenus/divisions', [
        'title' => ['fr' => 'Mode Lifestyle', 'en' => ''],
        'description' => ['fr' => 'Texte', 'en' => ''],
        'image' => UploadedFile::fake()->image('boutique.jpg', 800, 600),
        'image_alt' => ['fr' => 'Boutique', 'en' => ''],
        'categories' => ['fr' => "Mode\n\nSport ", 'en' => ''],
    ])->assertSessionHasNoErrors();

    $division = Division::latest('id')->first();
    expect($division->slug)->toBe('mode-lifestyle-2')
        ->and($division->categories['fr'])->toBe(['Mode', 'Sport']);

    $this->post('/admin/contenus/enseignes', [
        'name' => 'Zara', 'division_id' => $division->id, 'type' => 'franchise',
        'logo' => UploadedFile::fake()->image('zara.png', 300, 120),
    ])->assertSessionHasNoErrors();

    expect(Enseigne::where('name', 'Zara')->sole()->division_id)->toBe($division->id);
});

it('supprime l\'image envoyée mais jamais une image livrée avec le site', function () {
    Storage::fake('public');
    $this->actingAs($this->admin)->post('/admin/contenus/marques', [
        'name' => 'Nouvelle', 'category' => 'pgc', 'logo' => UploadedFile::fake()->image('logo.png', 200, 100), 'visible' => '1',
    ]);
    $brand = PartnerBrand::where('name', 'Nouvelle')->sole();
    $path = str($brand->logo)->after('storage/')->toString();
    Storage::disk('public')->assertExists($path);

    $this->delete("/admin/contenus/marques/{$brand->id}")->assertRedirect('/admin/contenus/marques');
    Storage::disk('public')->assertMissing($path);

    $original = PartnerBrand::where('name', 'Coca-Cola')->sole();
    $this->delete("/admin/contenus/marques/{$original->id}");
    expect(file_exists(public_path('images/partenaires/coca-cola.png')))->toBeTrue();
});

it('ne recharge pas les contenus par défaut au déploiement s\'ils existent', function () {
    PartnerBrand::first()->update(['name' => 'Modifiée dans le back office']);
    HeroSlide::query()->delete();

    $this->artisan('cedicom:seed-content')->assertSuccessful();

    expect(PartnerBrand::where('name', 'Modifiée dans le back office')->exists())->toBeTrue()
        ->and(HeroSlide::count())->toBe(3);
});
