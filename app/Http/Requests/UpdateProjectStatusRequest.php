<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Valida la variazione di stato del ciclo di vita di un progetto.
 */
class UpdateProjectStatusRequest extends FormRequest
{
    /**
     * Definisce le regole di validazione verificando la validità del nuovo stato del progetto.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ProjectStatus::class)],
        ];
    }

    /**
     * Un progetto avviato non torna pianificato; per concluderlo non devono esserci task aperti.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $next = $this->enum('status', ProjectStatus::class);
                $project = $this->route('project');

                // L'organizer interviene solo sui progetti senza un PM attivo: li conclude (con i task tutti
                // completati, controllo più sotto) o li archivia, oppure riattiva un concluso o un archiviato per poi assegnargli un nuovo PM.
                $reopening = ! $project->isEditable() && $next === ProjectStatus::InProgress;

                if (! $project->isManagedBy($this->user()) && ! in_array($next, [ProjectStatus::Finished, ProjectStatus::Archived], true) && ! $reopening) {
                    $validator->errors()->add('status', 'Senza un project manager attivo il progetto si può solo concludere, archiviare o riattivare.');

                    return;
                }

                if ($next === ProjectStatus::Planned && $project->status !== ProjectStatus::Planned) {
                    $validator->errors()->add('status', 'Un progetto avviato non può tornare pianificato.');

                    return;
                }

                if ($next !== ProjectStatus::Finished) {
                    return;
                }

                $open = $project->tasks()->where('status', '!=', TaskStatus::Completed)->count();

                if ($open > 0) {
                    $validator->errors()->add('status', "Il progetto non si può concludere: ci sono ancora {$open} task non completati.");
                }
            },
        ];
    }
}
