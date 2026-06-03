<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchBookDataFromIsbn;
use App\Models\Product;
use Illuminate\Console\Command;

class ReprocessBookIsbnCommand extends Command
{
    protected $signature = 'books:reprocess
                            {--solo-sin-titulo : Solo libros cuyo nombre sigue en mayúsculas (del CSV)}
                            {--limit= : Máximo de jobs a despachar}';

    protected $description = 'Re-despacha FetchBookDataFromIsbn para libros ya procesados';

    public function handle(): int
    {
        $query = Product::where('tipo_articulo', 2)
            ->whereHas('bookDetail', fn ($q) => $q->whereNotNull('google_books_synced_at'))
            ->with('bookDetail');

        if ($this->option('solo-sin-titulo')) {
            // Heurística: títulos del CSV van en mayúsculas o empiezan con prefijos como "(CAT)."
            $query->where(fn ($q) => $q
                ->whereRaw('name = UPPER(name)')
                ->orWhere('name', 'REGEXP', '^\\([A-Z0-9]+\\)\\.')
            );
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $total = $query->count();
        $this->line("Despachando jobs para <info>{$total}</info> libros...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById(200, function ($products) use ($bar) {
            foreach ($products as $product) {
                FetchBookDataFromIsbn::dispatch($product);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Listo. Ejecuta: php artisan queue:work --sleep=1");

        return self::SUCCESS;
    }
}
