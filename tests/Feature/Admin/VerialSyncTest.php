<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\VerialSyncLog;
use App\Services\Verial\VerialClient;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function regularUser(): User
{
    return User::factory()->create(['is_admin' => false]);
}

function mockVerialNotConfigured(): void
{
    app()->singleton(VerialClient::class, function () {
        return new VerialClient(host: null, port: 8000, session: null, timeout: 5);
    });
}

function mockVerialConfigured(): void
{
    app()->singleton(VerialClient::class, function () {
        return new VerialClient(host: '127.0.0.1', port: 8000, session: 'test-session', timeout: 5);
    });
}

test('verial index es accesible para el administrador', function () {
    mockVerialNotConfigured();

    $response = $this->actingAs(adminUser())->get(route('admin.verial.index'));

    $response->assertOk();
});

test('verial index no es accesible para usuario sin permisos de admin', function () {
    $response = $this->actingAs(regularUser())->get(route('admin.verial.index'));

    $response->assertForbidden();
});

test('index muestra alerta de no configurado cuando Verial no está configurado', function () {
    mockVerialNotConfigured();

    $response = $this->actingAs(adminUser())->get(route('admin.verial.index'));

    $response->assertOk()
        ->assertSee('Conector no configurado');
});

test('index muestra las cards de estadísticas', function () {
    mockVerialNotConfigured();

    $response = $this->actingAs(adminUser())->get(route('admin.verial.index'));

    $response->assertOk()
        ->assertSee('Productos sincronizados')
        ->assertSee('Sin sincronizar')
        ->assertSee('Última sincronización')
        ->assertSee('Errores');
});

test('sync catalog redirige correctamente tras ejecutar el comando', function () {
    // El comando sale limpiamente al ver que Verial no está configurado en tests
    $response = $this->actingAs(adminUser())
        ->post(route('admin.verial.sync-catalog'));

    $response->assertRedirect(route('admin.verial.index'));
    $response->assertSessionHas('success');
});

test('sync stock redirige correctamente tras ejecutar el comando', function () {
    $response = $this->actingAs(adminUser())
        ->post(route('admin.verial.sync-stock'));

    $response->assertRedirect(route('admin.verial.index'));
    $response->assertSessionHas('success');
});

test('send pending orders redirige correctamente tras ejecutar el comando', function () {
    $response = $this->actingAs(adminUser())
        ->post(route('admin.verial.send-orders'));

    $response->assertRedirect(route('admin.verial.index'));
    $response->assertSessionHas('success');
});
