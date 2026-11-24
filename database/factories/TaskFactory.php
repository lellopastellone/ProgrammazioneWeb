<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory per la generazione di task operativi dimostrativi.
 *
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    private const SAMPLE_TASKS = [
        ['API esportazione report e statistiche', 'Implementazione dell\'endpoint per l\'estrazione dei dati in formato CSV ed Excel per la rendicontazione.'],
        ['Ottimizzazione indici e query complesse', 'Analisi del piano di esecuzione delle query principali e inserimento di indici composti sulle foreign key.'],
        ['Revisione accessibilità e conformità WCAG', 'Controllo contrasti di colore, navigazione da tastiera e testi alternativi delle immagini sul portale.'],
        ['Configurazione template email di notifica', 'Creazione del layout responsive per le email transazionali inviate agli utenti del sistema.'],
        ['Refactoring gestione centralizzata errori', 'Unificazione del middleware per la cattura e formattazione uniforme delle eccezioni HTTP.'],
        ['Componente Blade modale di conferma', 'Sviluppo di una finestra modale riutilizzabile per confermare le operazioni di cancellazione o archivio.'],
        ['Test automatici di carico con traffico simulato', 'Script di test concorrente per verificare la tenuta della memoria e del database sotto stress.'],
        ['Aggiornamento documentazione tecnica e OpenAPI', 'Allineamento della documentazione Swagger con parametri di richiesta, header e codici di stato.'],
    ];

    /**
     * Definisce lo stato predefinito del modello Task con date e priorità casuali.
     */
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(0, 5));
        $sample = fake()->randomElement(self::SAMPLE_TASKS);

        return [
            'project_id' => Project::factory(),
            'title' => $sample[0] . ' ' . fake()->unique()->lexify('????'),
            'description' => $sample[1],
            'start_date' => $start,
            'end_date' => $start->copy()->addDays(fake()->numberBetween(1, 5)),
            'priority' => fake()->randomElement(Priority::cases()),
            'status' => TaskStatus::Todo,
        ];
    }

    /**
     * Imposta lo stato del task sul valore indicato.
     */
    public function status(TaskStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
