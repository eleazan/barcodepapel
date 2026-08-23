<?php

declare(strict_types=1);

use App\Jobs\FetchBookDataFromIsbn;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBookDetail;
use App\Models\User;
use App\Services\Books\GoogleBooksQuota;
use App\Services\Jobs\Tasks\BookCoverTask;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Cache::flush();
});

function usuarioAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function usuarioNormal(): User
{
    return User::factory()->create(['is_admin' => false]);
}

function libroPendiente(array $attrs = []): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'libros'],
        ['name' => 'Libros', 'is_active' => true, 'sort_order' => 0]
    );

    static $n = 0;
    $n++;

    return Product::create(array_merge([
        'category_id'   => $category->id,
        'name'          => 'Libro '.$n,
        'price'         => 10,
        'stock'         => 1,
        'is_active'     => false,
        'tipo_articulo' => 2,
        'barcode'       => '978000000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
    ], $attrs));
}

test('el panel de tareas es accesible para el administrador', function () {
    $response = $this->actingAs(usuarioAdmin())->get(route('admin.jobs.index'));

    $response->assertOk()
        ->assertSee('Portadas y fichas de libros');
});

test('el panel de tareas no es accesible sin permisos de admin', function () {
    $this->actingAs(usuarioNormal())
        ->get(route('admin.jobs.index'))
        ->assertForbidden();
});

test('el panel cuenta los libros pendientes', function () {
    libroPendiente();
    libroPendiente();
    libroPendiente(['image' => 'covers/ya-esta.jpg']);

    expect(app(BookCoverTask::class)->pendingCount())->toBe(2);

    $this->actingAs(usuarioAdmin())
        ->get(route('admin.jobs.index'))
        ->assertOk()
        ->assertSee('Pendientes');
});

test('lanzar el lote encola un job por libro pendiente', function () {
    Bus::fake();

    libroPendiente();
    libroPendiente();

    $response = $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 10]);

    $response->assertRedirect(route('admin.jobs.index'))
        ->assertSessionHas('success');

    Bus::assertBatchCount(1);
    Bus::assertBatched(function ($batch) {
        return $batch->name === 'portadas-libros' && $batch->jobs->count() === 2;
    });
});

test('la cantidad pedida limita el lote', function () {
    Bus::fake();

    libroPendiente();
    libroPendiente();
    libroPendiente();

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 2]);

    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 2);
});

test('no se encola nada cuando no hay pendientes', function () {
    Bus::fake();

    libroPendiente(['image' => 'covers/ya-esta.jpg']);

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 10])
        ->assertSessionHas('error');

    Bus::assertNothingBatched();
});

test('la cuota agotada impide lanzar el lote', function () {
    Bus::fake();
    config()->set('services.google_books.daily_quota', 1);

    libroPendiente();
    libroPendiente();
    app(GoogleBooksQuota::class)->hit();

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 10])
        ->assertSessionHas('error');

    Bus::assertNothingBatched();
});

test('la cuota restante recorta el tamaño del lote', function () {
    Bus::fake();
    config()->set('services.google_books.daily_quota', 3);

    libroPendiente();
    libroPendiente();
    libroPendiente();
    libroPendiente();
    app(GoogleBooksQuota::class)->hit();

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 100]);

    // Quedan 2 peticiones de cuota, así que solo salen 2 jobs
    Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 2);
});

test('la cantidad es obligatoria y numérica', function () {
    Bus::fake();

    libroPendiente();

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 'muchos'])
        ->assertSessionHasErrors('cantidad');

    Bus::assertNothingBatched();
});

test('no se lanza un segundo lote mientras hay uno en curso', function () {
    Bus::fake();

    libroPendiente();

    DB::table('job_batches')->insert([
        'id'             => 'lote-en-curso',
        'name'           => 'portadas-libros',
        'total_jobs'     => 10,
        'pending_jobs'   => 4,
        'failed_jobs'    => 0,
        'failed_job_ids' => '[]',
        'options'        => serialize([]),
        'created_at'     => now()->timestamp,
    ]);

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 10])
        ->assertSessionHas('error');

    Bus::assertNothingBatched();
});

test('el panel pinta el progreso del lote en curso', function () {
    DB::table('job_batches')->insert([
        'id'             => 'lote-visible',
        'name'           => 'portadas-libros',
        'total_jobs'     => 200,
        'pending_jobs'   => 50,
        'failed_jobs'    => 3,
        'failed_job_ids' => '[]',
        'options'        => serialize([]),
        'created_at'     => now()->timestamp,
    ]);

    $this->actingAs(usuarioAdmin())
        ->get(route('admin.jobs.index'))
        ->assertOk()
        ->assertSee('Lote en curso')
        ->assertSee('150')          // procesados
        ->assertSee('75 %')         // progreso
        ->assertSee('3 con error');
});

test('una tarea inexistente devuelve 404', function () {
    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'no-existe'), ['cantidad' => 1])
        ->assertNotFound();
});

test('reintentar descartados los devuelve a pendientes', function () {
    $product = libroPendiente();
    ProductBookDetail::create([
        'product_id'     => $product->id,
        'isbn'           => $product->barcode,
        'cover_attempts' => ProductBookDetail::MAX_COVER_ATTEMPTS,
    ]);

    $task = app(BookCoverTask::class);
    expect($task->pendingCount())->toBe(0)
        ->and($task->discardedCount())->toBe(1);

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.reset', 'portadas-libros'))
        ->assertSessionHas('success');

    expect($task->pendingCount())->toBe(1)
        ->and($task->discardedCount())->toBe(0);
});

test('un libro con portada archivada no vuelve a los pendientes', function () {
    $product = libroPendiente();
    ProductBookDetail::create([
        'product_id'       => $product->id,
        'isbn'             => $product->barcode,
        'cover_fetched_at' => now(),
        'cover_source'     => 'openlibrary',
    ]);

    expect(app(BookCoverTask::class)->pendingCount())->toBe(0);
});

test('los productos que no son libros quedan fuera de la tarea', function () {
    $category = Category::firstOrCreate(
        ['slug' => 'papeleria'],
        ['name' => 'Papelería', 'is_active' => true, 'sort_order' => 0]
    );

    Product::create([
        'category_id'   => $category->id,
        'name'          => 'Cuaderno',
        'price'         => 3,
        'stock'         => 10,
        'tipo_articulo' => 1,
        'barcode'       => '8412345678901',
    ]);

    expect(app(BookCoverTask::class)->pendingCount())->toBe(0);
});

test('el lote se encola escalonado para no saturar la API', function () {
    Bus::fake();
    config()->set('services.google_books.per_minute', 2);

    foreach (range(1, 5) as $i) {
        libroPendiente();
    }

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.run', 'portadas-libros'), ['cantidad' => 5]);

    Bus::assertBatched(function ($batch) {
        $delays = $batch->jobs->map(fn (FetchBookDataFromIsbn $job) => $job->delay)->all();
        $base   = $delays[0];

        $minutos = array_map(
            fn ($delay) => (int) round((float) $base->diffInMinutes($delay, absolute: true)),
            $delays
        );

        // Bloques de dos libros por minuto
        return $minutos === [0, 0, 1, 1, 2];
    });
});

test('cancelar un lote lo marca como cancelado', function () {
    DB::table('job_batches')->insert([
        'id'             => 'lote-a-cancelar',
        'name'           => 'portadas-libros',
        'total_jobs'     => 10,
        'pending_jobs'   => 4,
        'failed_jobs'    => 0,
        'failed_job_ids' => '[]',
        'options'        => serialize([]),
        'created_at'     => now()->timestamp,
    ]);

    $this->actingAs(usuarioAdmin())
        ->post(route('admin.jobs.cancel', ['task' => 'portadas-libros', 'batch' => 'lote-a-cancelar']))
        ->assertSessionHas('success');

    expect(DB::table('job_batches')->where('id', 'lote-a-cancelar')->value('cancelled_at'))
        ->not->toBeNull();
});
