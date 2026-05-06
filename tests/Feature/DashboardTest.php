<?php

declare(strict_types=1);

use App\Models\User;

test('dashboard is accessible to authenticated and verified users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('dashboard requires authentication', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('dashboard requires email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('verification.notice'));
});

test('dashboard displays user name', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSee('Jane Doe');
});
