<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory per la generazione di utenti fittizi nei test e nei seeder.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Password predefinita condivisa riutilizzata tra le istanze generate.
     */
    protected static ?string $password;

    /**
     * Definisce lo stato predefinito del modello User con dati anagrafici e credenziali casuali.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'active' => true,
        ];
    }

    /**
     * Imposta lo stato del modello impostando l'indirizzo email come non verificato.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Imposta lo stato del modello come utente disattivato.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }
}
