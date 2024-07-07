<?php

use App\Livewire\Other\GenerateSSHKeysButton;
use App\Models\User;

test('the button can be rendered', function (): void {

    Livewire::test(GenerateSSHKeysButton::class)
        ->assertStatus(200);
});

test('an administrator can click the button', function (): void {
    Toaster::fake();
    Config::set('auth.admin_email_addresses', ['admin@email.com']);

    $user = User::factory()->create(['email' => 'admin@email.com']);

    $this->actingAs($user);

    Artisan::shouldReceive('call')
        ->once()
        ->with('vanguard:generate-ssh-key')
        ->andReturn(0);

    Livewire::test(GenerateSSHKeysButton::class)
        ->call('generateKeys');

    $this->assertTrue($user->isAdmin());

    Toaster::assertDispatched(__('SSH key generation started. Please reload the page.'));
});

test('a regular user cannot click the button', function (): void {
    Toaster::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(GenerateSSHKeysButton::class)
        ->call('generateKeys');

    Toaster::assertDispatched(__('You are not authorized to generate SSH keys.'));
    $this->assertFalse($user->isAdmin());
});