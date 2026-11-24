<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Componente layout per le schermate pubbliche e di autenticazione.
 */
class GuestLayout extends Component
{
    /**
     * Inizializza il componente memorizzando l'eventuale titolo personalizzato della vista.
     */
    public function __construct(public ?string $title = null) {}

    /**
     * Restituisce la vista Blade associata al layout pubblico.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
