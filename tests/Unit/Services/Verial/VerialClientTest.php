<?php

declare(strict_types=1);

use App\Services\Verial\VerialClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

describe('VerialClient', function () {

    test('isConfigured() devuelve false cuando host es null', function () {
        $client = new VerialClient(host: null, port: 8000, session: 'abc', timeout: 30);

        expect($client->isConfigured())->toBeFalse();
    });

    test('isConfigured() devuelve false cuando session es null', function () {
        $client = new VerialClient(host: '127.0.0.1', port: 8000, session: null, timeout: 30);

        expect($client->isConfigured())->toBeFalse();
    });

    test('isConfigured() devuelve true cuando host y session están configurados', function () {
        $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'abc123', timeout: 30);

        expect($client->isConfigured())->toBeTrue();
    });

    test('get() lanza RuntimeException cuando no está configurado', function () {
        $client = new VerialClient(host: null, port: 8000, session: null, timeout: 30);

        expect(fn () => $client->get('GetArticulosWS'))
            ->toThrow(\RuntimeException::class, 'Verial no configurado');
    });

    test('post() lanza RuntimeException cuando no está configurado', function () {
        $client = new VerialClient(host: null, port: 8000, session: null, timeout: 30);

        expect(fn () => $client->post('NuevoDocClienteWS', []))
            ->toThrow(\RuntimeException::class, 'Verial no configurado');
    });

    test('get() realiza petición GET con parámetro de sesión', function () {
        Http::fake([
            'http://127.0.0.1:8000/GetArticulosWS*' => Http::response(['Articulos' => []], 200),
        ]);

        $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'mi-sesion', timeout: 30);
        $result = $client->get('GetArticulosWS');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'GetArticulosWS')
                && $request->url() === 'http://127.0.0.1:8000/GetArticulosWS?x=mi-sesion'
                || str_contains($request->url(), 'x=mi-sesion');
        });

        expect($result)->toBeArray();
    });

    test('post() realiza petición POST con cuerpo JSON y parámetro de sesión', function () {
        Http::fake([
            'http://127.0.0.1:8000/NuevoDocClienteWS*' => Http::response(['CodigoPedido' => 42], 200),
        ]);

        $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'mi-sesion', timeout: 30);
        $result = $client->post('NuevoDocClienteWS', ['NombreCliente' => 'Test']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'NuevoDocClienteWS')
                && $request->method() === 'POST';
        });

        expect($result)->toHaveKey('CodigoPedido', 42);
    });

    test('get() lanza RuntimeException en ConnectionException', function () {
        Http::fake([
            'http://127.0.0.1:8000/GetArticulosWS*' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'abc', timeout: 30);

        expect(fn () => $client->get('GetArticulosWS'))
            ->toThrow(\RuntimeException::class);
    });

});
