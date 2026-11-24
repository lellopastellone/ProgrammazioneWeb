<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida i dati per l'inserimento di un nuovo commento a un task.
 */
class StoreCommentRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per il testo del commento.
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:5000'],
        ];
    }
}
