<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Componente layout principale per le pagine dell'area autenticata.
 */
class AppLayout extends Component
{
    /**
     * Inizializza il componente memorizzando l'eventuale titolo personalizzato della vista.
     */
    public function __construct(public ?string $title = null) {}

    /**
     * Restituisce la vista Blade associata al layout principale.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
