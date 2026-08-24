<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Páginas legales de la tienda: aviso legal, política de privacidad y
 * condiciones de venta. Los datos del titular salen de config/tienda.php.
 */
class LegalController extends Controller
{
    public function notice(): View
    {
        return view('store.legal.aviso-legal');
    }

    public function privacy(): View
    {
        return view('store.legal.privacidad');
    }

    public function terms(): View
    {
        return view('store.legal.condiciones-de-venta');
    }
}
