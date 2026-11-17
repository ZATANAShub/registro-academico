<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Prepara la base de datos con la cuenta del operador.
     * No crea estudiantes: esos se registran desde la aplicación.
     */
    public function run(): void
    {
        $this->call(OperadorSeeder::class);
    }
}
