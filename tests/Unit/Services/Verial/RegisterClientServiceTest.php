<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Verial\RegisterClientService;
use App\Services\Verial\VerialClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeRegisterClientService(): RegisterClientService
{
    Http::fake([
        'http://127.0.0.1:8000/NuevoClienteWS*' => Http::response(['CodigoCliente' => 77], 200),
    ]);

    $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'test', timeout: 5);

    return new RegisterClientService($client);
}

describe('RegisterClientService', function () {

    test('register() llama a NuevoClienteWS con los datos del usuario', function () {
        $user    = User::factory()->create(['name' => 'Ana López', 'email' => 'ana@example.com']);
        $service = makeRegisterClientService();

        $service->register($user);

        Http::assertSent(function ($request) use ($user) {
            $body = $request->data();

            return str_contains($request->url(), 'NuevoClienteWS')
                && $body['Nombre'] === $user->name
                && $body['Email']  === $user->email;
        });
    });

    test('register() actualiza verial_cliente_id del usuario', function () {
        $user    = User::factory()->create(['verial_cliente_id' => null]);
        $service = makeRegisterClientService();

        $service->register($user);

        $this->assertDatabaseHas('users', [
            'id'                => $user->id,
            'verial_cliente_id' => 77,
        ]);
    });

    test('register() no hace nada si el usuario ya tiene verial_cliente_id', function () {
        $user    = User::factory()->create(['verial_cliente_id' => 50]);
        $service = makeRegisterClientService();

        $service->register($user);

        Http::assertNothingSent();

        $this->assertDatabaseHas('users', [
            'id'                => $user->id,
            'verial_cliente_id' => 50,
        ]);
    });

});
