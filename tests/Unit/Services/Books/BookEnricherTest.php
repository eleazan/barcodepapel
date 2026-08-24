<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBookDetail;
use App\Services\Books\BookEnricher;
use App\Services\Books\CoverOutcome;
use App\Services\Books\GoogleBooksQuota;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Cache::flush();
});

function libro(array $attrs = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'libros'],
        ['name' => 'Libros', 'is_active' => true, 'sort_order' => 0]
    );

    return Product::create(array_merge([
        'category_id'   => $category->id,
        'name'          => 'EL QUIJOTE',
        'slug'          => 'el-quijote-'.uniqid(),
        'price'         => 12.50,
        'stock'         => 3,
        'is_active'     => false,
        'tipo_articulo' => 2,
        'barcode'       => '9788491050294',
    ], $attrs));
}

function respuestaGoogle(array $volumeInfo): array
{
    return ['items' => [['volumeInfo' => $volumeInfo]]];
}

function enricher(): BookEnricher
{
    return app(BookEnricher::class);
}

test('descarga la portada de Google Books y guarda la ficha', function () {
    $product = libro();

    Http::fake([
        'www.googleapis.com/*' => Http::response(respuestaGoogle([
            'title'         => 'Don Quijote de la Mancha',
            'language'      => 'es',
            'authors'       => ['Miguel de Cervantes'],
            'publisher'     => 'Cátedra',
            'pageCount'     => 1250,
            'publishedDate' => '2015-03-01',
            'description'   => 'La novela.',
            'imageLinks'    => ['large' => 'http://books.google.com/portada-grande.jpg'],
        ])),
        'books.google.com/*' => Http::response('binario-jpeg', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::Obtenida);

    $product->refresh();
    expect($product->image)->toBe('covers/9788491050294.jpg')
        ->and($product->is_active)->toBeTrue()
        ->and($product->name)->toBe('Don Quijote de la Mancha')
        ->and($product->description)->toBe('La novela.');

    Storage::disk('public')->assertExists('covers/9788491050294.jpg');

    $detail = $product->bookDetail;
    expect($detail->autores)->toBe('Miguel de Cervantes')
        ->and($detail->editorial)->toBe('Cátedra')
        ->and($detail->paginas)->toBe(1250)
        ->and($detail->anio_publicacion)->toBe(2015)
        ->and($detail->cover_source)->toBe('google_books')
        ->and($detail->cover_fetched_at)->not->toBeNull()
        ->and($detail->cover_attempts)->toBe(0);
});

test('prefiere la imagen de mayor tamaño disponible', function () {
    $product = libro();

    Http::fake([
        'www.googleapis.com/*' => Http::response(respuestaGoogle([
            'imageLinks' => [
                'thumbnail'  => 'https://books.google.com/pequena.jpg',
                'large'      => 'https://books.google.com/grande.jpg',
                'extraLarge' => 'https://books.google.com/enorme.jpg',
            ],
        ])),
        '*' => Http::response('binario', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    enricher()->enrich($product);

    Http::assertSent(fn ($request) => $request->url() === 'https://books.google.com/enorme.jpg');
});

test('recurre a OpenLibrary cuando Google no tiene portada', function () {
    $product = libro();

    Http::fake([
        'www.googleapis.com/*' => Http::response(respuestaGoogle([
            'title'    => 'Un título',
            'language' => 'en',
            'authors'  => ['Alguien'],
        ])),
        'covers.openlibrary.org/*' => Http::response('jpeg-de-openlibrary', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::Obtenida);

    $product->refresh();
    expect($product->bookDetail->cover_source)->toBe('openlibrary')
        // El título en inglés no sustituye al del ERP
        ->and($product->name)->toBe('EL QUIJOTE')
        // pero los metadatos sí se aprovechan
        ->and($product->bookDetail->autores)->toBe('Alguien');

    Storage::disk('public')->assertExists('covers/9788491050294.jpg');
});

test('cuenta el intento cuando ninguna fuente tiene portada', function () {
    $product = libro();

    Http::fake([
        'www.googleapis.com/*'     => Http::response(respuestaGoogle([])),
        'covers.openlibrary.org/*' => Http::response('', 404),
    ]);

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::SinPortada);

    $detail = $product->fresh()->bookDetail;
    expect($detail->cover_attempts)->toBe(1)
        ->and($detail->cover_fetched_at)->toBeNull()
        ->and($detail->google_books_synced_at)->not->toBeNull();
});

test('los intentos se acumulan hasta descartar el libro', function () {
    $product = libro();

    Http::fake([
        'www.googleapis.com/*'     => Http::response(respuestaGoogle([])),
        'covers.openlibrary.org/*' => Http::response('', 404),
    ]);

    for ($i = 0; $i < 3; $i++) {
        enricher()->enrich($product->fresh());
    }

    $detail = $product->fresh()->bookDetail;
    expect($detail->cover_attempts)->toBe(3)
        ->and($detail->coverDiscarded())->toBeTrue();
});

test('no vuelve a consultar un libro que ya tiene portada local', function () {
    $product = libro(['image' => 'covers/9788491050294.jpg']);

    Http::fake();

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::YaTenia);
    Http::assertNothingSent();

    expect($product->fresh()->bookDetail->cover_fetched_at)->not->toBeNull();
});

test('una portada remota antigua se descarga al disco propio', function () {
    $product = libro(['image' => 'https://books.google.com/antigua.jpg']);

    Http::fake([
        'www.googleapis.com/*' => Http::response(respuestaGoogle([
            'imageLinks' => ['thumbnail' => 'https://books.google.com/nueva.jpg'],
        ])),
        '*' => Http::response('binario', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::Obtenida)
        ->and($product->fresh()->image)->toBe('covers/9788491050294.jpg');
});

test('con refresh actualiza la ficha sin tocar la portada existente', function () {
    $product = libro(['image' => 'covers/9788491050294.jpg']);

    Http::fake([
        'www.googleapis.com/*' => Http::response(respuestaGoogle([
            'title'    => 'Don Quijote de la Mancha',
            'language' => 'es',
            'authors'  => ['Miguel de Cervantes'],
        ])),
    ]);

    $outcome = enricher()->enrich($product, refresh: true);

    expect($outcome)->toBe(CoverOutcome::YaTenia);

    $product->refresh();
    expect($product->name)->toBe('Don Quijote de la Mancha')
        ->and($product->image)->toBe('covers/9788491050294.jpg')
        ->and($product->bookDetail->autores)->toBe('Miguel de Cervantes');
});

test('sin código de barras no se consulta nada', function () {
    $product = libro(['barcode' => null]);

    Http::fake();

    expect(enricher()->enrich($product))->toBe(CoverOutcome::SinIsbn);
    Http::assertNothingSent();
});

test('un 429 de Google no cuenta como intento fallido', function () {
    $product = libro();

    Http::fake([
        'www.googleapis.com/*'     => Http::response('', 429),
        'covers.openlibrary.org/*' => Http::response('', 404),
    ]);

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::LimiteAlcanzado);

    // El libro sigue pendiente: no se ha marcado ningún intento
    expect($product->fresh()->bookDetail)->toBeNull();
});

test('con la cuota agotada no se pregunta a Google pero sí a OpenLibrary', function () {
    $product = libro();
    config()->set('services.google_books.daily_quota', 2);

    $quota = app(GoogleBooksQuota::class);
    $quota->hit();
    $quota->hit();

    Http::fake([
        'covers.openlibrary.org/*' => Http::response('jpeg', 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $outcome = enricher()->enrich($product);

    expect($outcome)->toBe(CoverOutcome::Obtenida);
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'googleapis.com'));
});

test('cada consulta a Google consume cuota', function () {
    $product = libro();
    $quota   = app(GoogleBooksQuota::class);

    Http::fake([
        'www.googleapis.com/*'     => Http::response(respuestaGoogle([])),
        'covers.openlibrary.org/*' => Http::response('', 404),
    ]);

    expect($quota->used())->toBe(0);

    enricher()->enrich($product);

    expect($quota->used())->toBe(1);
});

test('reutiliza la ficha que ya venía del ERP', function () {
    $product = libro();
    ProductBookDetail::create([
        'product_id' => $product->id,
        'isbn'       => '9788491050294',
        'coleccion'  => 'Clásicos',
    ]);

    Http::fake([
        'www.googleapis.com/*'     => Http::response(respuestaGoogle(['authors' => ['Cervantes']])),
        'covers.openlibrary.org/*' => Http::response('', 404),
    ]);

    enricher()->enrich($product->fresh());

    expect(ProductBookDetail::where('product_id', $product->id)->count())->toBe(1)
        ->and($product->fresh()->bookDetail->coleccion)->toBe('Clásicos');
});
