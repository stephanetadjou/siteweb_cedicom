<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PartnershipRequestController;
use Illuminate\Support\Facades\Route;

/*
| Back office /admin, réservé aux comptes administrateurs
| (créés avec : php artisan cedicom:admin adresse@exemple.com).
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:10,1');
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
    });
});
