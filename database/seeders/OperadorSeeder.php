<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea (o actualiza) la cuenta del operador que inicia sesión en el sistema.
 * Los datos se leen del archivo .env para no escribir la contraseña en el código.
 */
class OperadorSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('OPERADOR_EMAIL');
        $password = env('OPERADOR_PASSWORD');

        if (empty($email) || empty($password)) {
            $this->command->error('Define OPERADOR_EMAIL y OPERADOR_PASSWORD en el archivo .env');

            return;
        }

        // El modelo User guarda la contraseña con hash automáticamente (cast "hashed").
        User::updateOrCreate(
            ['email' => $email],
            ['name' => env('OPERADOR_NOMBRE', 'Operador'), 'password' => $password]
        );

        $this->command->info("Operador listo: {$email}");
    }
}
