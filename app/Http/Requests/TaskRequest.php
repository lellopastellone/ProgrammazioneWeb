<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use App\Models\Iteration;
use App\Models\Project;
use App\Rules\UniqueIgnoringCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Valida la creazione e l'aggiornamento dei dettagli operativi di un task di progetto.
 */
class TaskRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per titolo, date, priorità e iterazione.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'bail',
                'required',
                'string',
                'max:255',
                new UniqueIgnoringCase($this->project()->tasks(), 'title', $this->route('task'), 'Esiste già un task con questo titolo nel progetto.'),
            ],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'iteration_id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists(Iteration::class, 'id')->where('project_id', $this->project()->id),
            ],
        ];
    }

    /**
     * Valida che le date del task rientrino nell'intervallo temporale dell'iterazione selezionata.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Campo assente = il task resta nella sua iterazione: le nuove date vanno controllate su quella.
                $iterationId = $this->input('iteration_id', $this->route('task')?->iteration_id);

                if ($validator->errors()->isNotEmpty() || ! $iterationId) {
                    return;
                }

                $iteration = Iteration::find($iterationId);

                foreach (['start_date', 'end_date'] as $field) {
                    if (! $iteration->includes($this->date($field))) {
                        $validator->errors()->add($field, "La data deve essere compresa nell'iterazione ({$iteration->starts_on->toDateString()} - {$iteration->ends_on->toDateString()}).");
                    }
                }
            },
        ];
    }

    /**
     * Risolve l'istanza del progetto a cui appartiene il task.
     */
    private function project(): Project
    {
        return $this->route('project') ?? $this->route('task')->project;
    }
}
