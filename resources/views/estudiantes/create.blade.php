@extends('layouts.app')

@section('titulo', 'Registrar estudiante')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h2 class="h4 mb-0">Formulario de Estudiante</h2>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted">Completa los siguientes datos:</p>

                    <form action="{{ route('estudiantes.store') }}" method="POST" novalidate>
                        @csrf
                        @include('estudiantes._formulario', ['estudiante' => null])

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Guardar estudiante</button>
                            <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">Ver estudiantes</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
