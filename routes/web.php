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
    
    Route::livewire('companies', 'pages::companies.index')->middleware('permission:companies.view')->name('companies.index');
    Route::livewire('companies/create', 'pages::companies.create')->middleware('permission:companies.create')->name('companies.create');
    Route::livewire('companies/{company}/edit', 'pages::companies.edit')->middleware('permission:companies.edit')->name('companies.edit');

    Route::livewire('branches', 'pages::branches.index')->middleware('permission:branches.view')->name('branches.index');
    Route::livewire('branches/create', 'pages::branches.create')->middleware('permission:branches.create')->name('branches.create');
    Route::livewire('branches/{branch}/edit', 'pages::branches.edit')->middleware('permission:branches.edit')->name('branches.edit');

    Route::livewire('menus', 'pages::menus.index')->middleware('permission:menus.view')->name('menus.index');
    Route::livewire('menus/create', 'pages::menus.create')->middleware('permission:menus.create')->name('menus.create');
    Route::livewire('menus/{menu}/edit', 'pages::menus.edit')->middleware('permission:menus.edit')->name('menus.edit');

    Route::livewire('configurations/parameters', 'pages::configurations.parameters.index')->middleware('permission:parameters.view')->name('parameters.index');
});

require __DIR__.'/settings.php';
