<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Rules\UniqueIgnoringCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Valida la creazione e l'aggiornamento di un'iterazione (sprint) di progetto.
 * Controlla unicità del nome, coerenza temporale e compatibilità con i task assegnati.
 */
class IterationRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione per nome e date dell'iterazione.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
                new UniqueIgnoringCase($this->project()->iterations(), 'name', $this->route('iteration'), "Esiste già un'iterazione con questo nome nel progetto."),
            ],
            'starts_on' => ['required', 'date', ...$this->notInThePast('starts_on')],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on', ...$this->notInThePast('ends_on')],
        ];
    }

    /**
     * Personalizza i messaggi di errore per i vincoli sulle date di inizio e fine.
     */
    public function messages(): array
    {
        return [
            'starts_on.after_or_equal' => "L'inizio non può essere prima di oggi.",
            'ends_on.after_or_equal' => 'La fine deve essere dopo l\'inizio e non prima di oggi.',
        ];
    }

    /**
     * Impone che la data non sia nel passato per nuove iterazioni o date modificate,
     * ma consente di aggiornare iterazioni già in corso se la data non viene toccata.
     */
    private function notInThePast(string $field): array
    {
        $iteration = $this->route('iteration');

        return $iteration && $this->input($field) === $iteration->{$field}->toDateString() ? [] : ['after_or_equal:today'];
    }

    /**
     * Esegue validazioni aggiuntive su sovrapposizioni temporali e copertura dei task esistenti.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $iteration = $this->route('iteration');

                // Due periodi si sovrappongono se ciascuno inizia prima che l'altro finisca (estremi compresi).
                $overlapping = $this->project()->iterations()
                    ->when($iteration, fn (Builder $q) => $q->whereKeyNot($iteration->id))
                    ->where('starts_on', '<=', $this->date('ends_on'))
                    ->where('ends_on', '>=', $this->date('starts_on'))
                    ->first();

                if ($overlapping) {
                    $validator->errors()->add('starts_on', "Le date si sovrappongono all'iterazione {$overlapping->name} ({$overlapping->starts_on->toDateString()} - {$overlapping->ends_on->toDateString()}).");

                    return;
                }

                // In fase di creazione l'iterazione non ha ancora task assegnati: la validazione termina qui.
                if (! $iteration) {
                    return;
                }

                $outside = $iteration->tasks()
                    ->where(fn (Builder $q) => $q
                        ->where('start_date', '<', $this->date('starts_on'))
                        ->orWhere('end_date', '>', $this->date('ends_on')))
                    ->exists();

                if ($outside) {
                    $validator->errors()->add('starts_on', "Alcuni task dell'iterazione resterebbero fuori dalle nuove date.");
                }
            },
        ];
    }

    /**
     * Risolve l'istanza del progetto associato dalla rotta corrente.
     */
    private function project(): Project
    {
        return $this->route('project') ?? $this->route('iteration')->project;
    }
}
