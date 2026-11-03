<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PartnershipController;
use Illuminate\Support\Facades\Route;

/*
| Site vitrine CEDICOM Group (React via Inertia + Tailwind), bilingue FR / EN.
| Les routes Breeze d'origine (dashboard, profil, inscription, routes/auth.php)
| restent désactivées. Seul le back office (/admin, routes/admin.php) a une
| connexion, réservée aux comptes administrateurs.
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/langue/{locale}', LocaleController::class)->name('locale');

Route::get('/devenir-partenaire', [PartnershipController::class, 'create'])->name('partner.create');
Route::post('/devenir-partenaire', [PartnershipController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('partner.store');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

require __DIR__.'/admin.php';
