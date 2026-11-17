<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * CRUD de estudiantes: listar/buscar, registrar, editar y eliminar.
 */
class EstudianteController extends Controller
{
    /**
     * Listado con búsqueda por nombre y paginación.
     */
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $estudiantes = Estudiante::query()
            ->when($buscar !== '', fn ($consulta) => $consulta->where('nombre', 'like', "%{$buscar}%"))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('estudiantes.index', compact('estudiantes', 'buscar'));
    }

    /**
     * Formulario de registro.
     */
    public function create(): View
    {
        return view('estudiantes.create', ['programas' => config('programas')]);
    }

    /**
     * Valida y guarda un estudiante nuevo.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas(), $this->mensajes());

        Estudiante::create($datos);

        return redirect()->route('estudiantes.index')
            ->with('exito', 'Estudiante registrado correctamente.');
    }

    /**
     * Formulario de edición. Si el id no existe, Laravel responde 404.
     */
    public function edit(Estudiante $estudiante): View
    {
        return view('estudiantes.edit', [
            'estudiante' => $estudiante,
            'programas' => config('programas'),
        ]);
    }

    /**
     * Valida y actualiza la misma fila (conserva su id).
     */
    public function update(Request $request, Estudiante $estudiante): RedirectResponse
    {
        $datos = $request->validate($this->reglas($estudiante), $this->mensajes());

        $estudiante->update($datos);

        return redirect()->route('estudiantes.index')
            ->with('exito', 'Estudiante actualizado correctamente.');
    }

    /**
     * Elimina solamente el estudiante seleccionado.
     */
    public function destroy(Estudiante $estudiante): RedirectResponse
    {
        $estudiante->delete();

        return redirect()->route('estudiantes.index')
            ->with('exito', 'Estudiante eliminado correctamente.');
    }

    /**
     * Reglas de validación del Encuentro 16.
     * Al editar se ignora el correo del propio estudiante para que pueda conservarlo.
     */
    private function reglas(?Estudiante $estudiante = null): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:80'],
            'correo' => [
                'required', 'email', 'max:120',
                Rule::unique('estudiantes', 'correo')->ignore($estudiante?->id),
            ],
            'edad' => ['required', 'integer', 'between:16,100'],
            'programa' => ['required', Rule::in(config('programas'))],
        ];
    }

    /**
     * Mensajes de error en español.
     */
    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede superar los 80 caracteres.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'Escribe un correo válido.',
            'correo.max' => 'El correo no puede superar los 120 caracteres.',
            'correo.unique' => 'Ya existe un estudiante con ese correo.',
            'edad.required' => 'La edad es obligatoria.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.between' => 'La edad debe estar entre 16 y 100 años.',
            'programa.required' => 'Selecciona un programa.',
            'programa.in' => 'El programa seleccionado no es válido.',
        ];
    }
}
