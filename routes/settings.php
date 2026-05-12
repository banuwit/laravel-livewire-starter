<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('security.edit');

    Route::livewire('permissions', 'pages::permissions.index')->name('permissions.index');
    Route::livewire('permissions/create', 'pages::permissions.create')->name('permissions.create');
    Route::livewire('permissions/{permission}/edit', 'pages::permissions.edit')->name('permissions.edit');

    Route::livewire('roles', 'pages::roles.index')->name('roles.index');
    Route::livewire('roles/create', 'pages::roles.create')->name('roles.create');
    Route::livewire('roles/{role}/edit', 'pages::roles.edit')->name('roles.edit');
    Route::livewire('roles/{role}/permissions', 'pages::roles.permissions')->name('roles.permissions');
});
