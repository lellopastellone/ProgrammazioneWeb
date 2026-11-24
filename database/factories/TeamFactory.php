<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory per la generazione di team tecnici aziendali fittizi.
 *
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Definisce lo stato predefinito del modello Team con nome e descrizione casuali.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'description' => fake()->sentence(),
        ];
    }
}
