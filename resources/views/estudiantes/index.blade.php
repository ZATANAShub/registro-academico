@extends('layouts.app')

@section('titulo', 'Estudiantes')

@section('contenido')
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h2 class="h4 mb-0">Estudiantes registrados</h2>
            <a href="{{ route('estudiantes.create') }}" class="btn btn-primary btn-sm">Registrar nuevo estudiante</a>
        </div>
        <div class="card-body">
            <form action="{{ route('estudiantes.index') }}" method="GET" class="row g-2 mb-3" role="search">
                <div class="col-12 col-sm">
                    <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                           placeholder="Buscar por nombre…" aria-label="Buscar por nombre">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Buscar</button>
                    @if ($buscar !== '')
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>

            @if ($estudiantes->isEmpty())
                <div class="alert alert-info mb-0">
                    @if ($buscar !== '')
                        No se encontraron estudiantes con el nombre «{{ $buscar }}».
                    @else
                        Todavía no hay estudiantes registrados.
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Edad</th>
                                <th>Programa</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($estudiantes as $estudiante)
                                <tr>
                                    <td>{{ $estudiante->id }}</td>
                                    <td>{{ $estudiante->nombre }}</td>
                                    <td>{{ $estudiante->correo }}</td>
                                    <td>{{ $estudiante->edad }}</td>
                                    <td>{{ $estudiante->programa }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('estudiantes.edit', $estudiante) }}" class="btn btn-warning btn-sm">Editar</a>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#eliminar-{{ $estudiante->id }}">
                                            Eliminar
                                        </button>

                                        {{-- Ventana de confirmación: "Cancelar" solo la cierra; no envía nada al servidor. --}}
                                        <div class="modal fade" id="eliminar-{{ $estudiante->id }}" tabindex="-1"
                                             aria-labelledby="titulo-eliminar-{{ $estudiante->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-wrap">
                                                    <div class="modal-header">
                                                        <h3 class="modal-title fs-5" id="titulo-eliminar-{{ $estudiante->id }}">Confirmar eliminación</h3>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿Seguro que deseas eliminar a <strong>{{ $estudiante->nombre }}</strong>
                                                        ({{ $estudiante->correo }})? Esta acción no se puede deshacer.
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form action="{{ route('estudiantes.destroy', $estudiante) }}" method="POST" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $estudiantes->links() }}
            @endif
        </div>
    </div>
@endsection
