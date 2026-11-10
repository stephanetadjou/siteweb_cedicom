<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\EnseigneController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\PartnerBrandController;
use App\Http\Controllers\Admin\PartnershipRequestController;
use App\Http\Controllers\Admin\PoleController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
| Back office /admin, réservé aux comptes administrateurs
| (créés avec : php artisan cedicom:admin adresse@exemple.com).
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:5,1');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        foreach (['partenariats' => PartnershipRequestController::class, 'messages' => ContactMessageController::class] as $uri => $controller) {
            Route::get($uri, [$controller, 'index'])->name("$uri.index");
            Route::get("$uri/export", [$controller, 'export'])->name("$uri.export");
            Route::get("$uri/{id}", [$controller, 'show'])->whereNumber('id')->name("$uri.show");
            Route::patch("$uri/{id}/statut", [$controller, 'toggle'])->whereNumber('id')->name("$uri.toggle");
        }

        Route::get('parametres', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('parametres', [SettingsController::class, 'update'])->name('settings.update');

        $contents = [
            'diaporama' => HeroSlideController::class,
            'poles' => PoleController::class,
            'divisions' => DivisionController::class,
            'enseignes' => EnseigneController::class,
            'marques' => PartnerBrandController::class,
        ];
        foreach ($contents as $uri => $controller) {
            Route::prefix("contenus/$uri")->name("contenus.$uri.")->whereNumber('id')->group(function () use ($controller) {
                Route::get('/', [$controller, 'index'])->name('index');
                Route::get('ajouter', [$controller, 'create'])->name('create');
                Route::post('/', [$controller, 'store'])->name('store');
                Route::get('{id}/modifier', [$controller, 'edit'])->name('edit');
                Route::put('{id}', [$controller, 'update'])->name('update');
                Route::patch('{id}/{direction}', [$controller, 'move'])->whereIn('direction', ['monter', 'descendre'])->name('move');
                Route::delete('{id}', [$controller, 'destroy'])->name('destroy');
            });
        }
    });
});
