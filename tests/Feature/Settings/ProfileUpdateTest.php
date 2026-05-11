<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->withEmployee()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('account information can be updated', function () {
    $user = User::factory()->withEmployee()->create();

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
    $user = User::factory()->withEmployee()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('username', 'sameuser')
        ->set('email', $user->email)
        ->call('updateAccount');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('employee profile can be updated', function () {
    $user = User::factory()->withEmployee()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Updated Name')
        ->set('gender', 'female')
        ->call('updateEmployee');

    $response->assertHasNoErrors();

    expect($user->employee->refresh()->name)->toEqual('Updated Name');
    expect($user->employee->refresh()->gender)->toEqual('female');
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
