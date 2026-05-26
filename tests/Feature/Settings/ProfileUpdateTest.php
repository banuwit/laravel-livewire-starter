<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->withProfile()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('account information can be updated', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('username', 'testuser')
        ->set('email', 'test@example.com')
        ->call('updateAccount');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->username)->toEqual('testuser');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('username', 'sameuser')
        ->set('email', $user->email)
        ->call('updateAccount');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('profile data can be updated', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('religion', 'islam')
        ->set('marital_status', 'single')
        ->call('updateProfileData');

    $response->assertHasNoErrors();

    expect($user->profile->refresh()->religion)->toEqual('islam');
    expect($user->profile->refresh()->marital_status)->toEqual('single');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
