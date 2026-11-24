<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory per la generazione di commenti fittizi associati ai task.
 *
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    private const SAMPLE_COMMENTS = [
        'Merge request aperta su GitLab: il codice è pronto per la revisione.',
        'Verificato in ambiente di staging con dati di collaudo: i test hanno dato esito positivo.',
        'Sto lavorando alla risoluzione del bug segnalato; rilascio un fix entro la giornata.',
        'Aggiornate le librerie e verificata la retrocompatibilità con i componenti esistenti.',
        'Ho completato l\'implementazione secondo i requisiti concordati con il PM.',
        'Test di carico e vulnerabilità superati senza anomalie riscontrate.',
    ];

    /**
     * Definisce lo stato predefinito del modello Comment per test e seeder.
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'created_by' => User::factory(),
            'text' => fake()->randomElement(self::SAMPLE_COMMENTS),
        ];
    }
}
