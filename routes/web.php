<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/faithamins', 'faithamins')->name('faithamins');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Route::get('users', App\Livewire\Users\Index::class)->name('users.index');
    // Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::livewire('users', 'pages::users.index')->middleware('permission:users.view')->name('users.index');
    Route::livewire('users/create', 'pages::users.create')->middleware('permission:users.create')->name('users.create');
    Route::livewire('users/{user}/edit', 'pages::users.edit')->middleware('permission:users.edit')->name('users.edit');
    Route::livewire('users/{user}/roles', 'pages::users.roles')->middleware('permission:users.assign_roles')->name('users.roles');
    
    Route::livewire('organizations', 'pages::organizations.index')->middleware('permission:organizations.view')->name('organizations.index');
    Route::livewire('organizations/create', 'pages::organizations.create')->middleware('permission:organizations.create')->name('organizations.create');
    Route::livewire('organizations/{organization}/edit', 'pages::organizations.edit')->middleware('permission:organizations.edit')->name('organizations.edit');

    Route::livewire('branches', 'pages::branches.index')->middleware('permission:branches.view')->name('branches.index');
    Route::livewire('branches/create', 'pages::branches.create')->middleware('permission:branches.create')->name('branches.create');
    Route::livewire('branches/{branch}/edit', 'pages::branches.edit')->middleware('permission:branches.edit')->name('branches.edit');

    Route::livewire('menus', 'pages::menus.index')->middleware('permission:menus.view')->name('menus.index');
    Route::livewire('menus/create', 'pages::menus.create')->middleware('permission:menus.create')->name('menus.create');
    Route::livewire('menus/{menu}/edit', 'pages::menus.edit')->middleware('permission:menus.edit')->name('menus.edit');

    Route::livewire('configurations/parameters', 'pages::configurations.parameters.index')->middleware('permission:parameters.view')->name('parameters.index');

    Route::livewire('activity-logs', 'pages::activity-logs.index')->middleware('permission:activity_logs.view')->name('activity-logs.index');

    Route::livewire('notifications', 'pages::notifications.index')->name('notifications.index');
});

require __DIR__.'/settings.php';
