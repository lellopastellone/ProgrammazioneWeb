<?php

namespace Database\Factories;

use App\Models\Iteration;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory per la generazione di iterazioni di progetto fittizie.
 *
 * @extends Factory<Iteration>
 */
class IterationFactory extends Factory
{
    /**
     * Definisce lo stato predefinito del modello Iteration per test e seeder.
     */
    public function definition(): array
    {
        $start = now()->startOfWeek();

        return [
            'project_id' => Project::factory(),
            'name' => 'Sprint '.fake()->unique()->numberBetween(1, 999),
            'starts_on' => $start,
            'ends_on' => $start->copy()->addWeeks(2),
        ];
    }
}
