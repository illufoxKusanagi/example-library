<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated admin can visit the dashboard with library analytics', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Catalog Titles')
        ->assertSee('Active Borrowings')
        ->assertSee('Registered Members');
});

test('authenticated client member can visit the dashboard with loan allowance and quota', function () {
    $client = User::factory()->create(['role' => 'client']);

    $response = $this->actingAs($client)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Your Loan Allowance')
        ->assertSee('Available to Borrow');
});
