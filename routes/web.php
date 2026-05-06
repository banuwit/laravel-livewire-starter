<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Route::get('users', App\Livewire\Users\Index::class)->name('users.index');
    // Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::livewire('users', 'pages::users.index')->middleware('permission:users.view')->name('users.index');
    Route::livewire('users/create', 'pages::users.create')->middleware('permission:users.create')->name('users.create');
    Route::livewire('users/{user}/edit', 'pages::users.edit')->middleware('permission:users.edit')->name('users.edit');
    Route::livewire('users/{user}/roles', 'pages::users.roles')->middleware('permission:users.assign_roles')->name('users.roles');

    Route::livewire('posts', 'pages::posts.index')->name('posts.index');
    Route::livewire('posts/create', 'pages::posts.create')->name('posts.create');
    Route::livewire('posts/{post}/edit', 'pages::posts.edit')->name('posts.edit');

    Route::livewire('menus', 'pages::menus.index')->middleware('permission:menus.view')->name('menus.index');
    Route::livewire('menus/create', 'pages::menus.create')->middleware('permission:menus.create')->name('menus.create');
    Route::livewire('menus/{menu}/edit', 'pages::menus.edit')->middleware('permission:menus.edit')->name('menus.edit');
});

require __DIR__.'/settings.php';
