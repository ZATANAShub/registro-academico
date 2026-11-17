<?php

namespace Database\Factories;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Genera estudiantes ficticios para las pruebas automáticas.
 *
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'edad' => fake()->numberBetween(16, 60),
            'programa' => fake()->randomElement(config('programas')),
        ];
    }
}
