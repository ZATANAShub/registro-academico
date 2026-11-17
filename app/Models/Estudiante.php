<?php

namespace App\Models;

use Database\Factories\EstudianteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent de la tabla "estudiantes".
 */
#[Fillable(['nombre', 'correo', 'edad', 'programa'])]
class Estudiante extends Model
{
    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'edad' => 'integer',
        ];
    }
}
