<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Valida il passaggio di stato di un task applicando le regole della macchina a stati.
 */
class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione verificando la validità dello stato inviato.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TaskStatus::class)],
        ];
    }

    /**
     * Valida che la transizione richiesta sia ammessa per il ruolo dell'utente che la richiede.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $task = $this->route('task');
                $isPm = $this->user()->can('manage', $task);

                if (! $task->status->canTransitionTo($this->enum('status', TaskStatus::class), $isPm)) {
                    $validator->errors()->add('status', "Transizione da {$task->status->value} a {$this->input('status')} non consentita.");
                }
            },
        ];
    }
}
