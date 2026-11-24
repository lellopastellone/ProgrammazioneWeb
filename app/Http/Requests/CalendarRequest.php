<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Gestisce e valida il parametro del mese per la consultazione dei calendari.
 */
class CalendarRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per il formato del mese richiesto.
     */
    public function rules(): array
    {
        return [
            // Limite inferiore: l'anno 0000 passa date_format ma PostgreSQL lo rifiuta (errore 500).
            'month' => ['nullable', 'date_format:Y-m', 'after_or_equal:2000-01'],
        ];
    }

    /**
     * Risolve il mese richiesto come CarbonImmutable o restituisce il mese corrente di default.
     * Legge il valore validato (non la query grezza: con un body JSON i due possono differire).
     */
    public function month(): CarbonImmutable
    {
        $month = $this->validated('month');

        return $month
            ? CarbonImmutable::createFromFormat('!Y-m', $month)
            : CarbonImmutable::now()->startOfMonth();
    }
}
