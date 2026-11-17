@extends('layouts.app')

@section('titulo', 'Editar estudiante')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-warning">
                    <h2 class="h4 mb-0">Editar estudiante #{{ $estudiante->id }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('estudiantes.update', $estudiante) }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')
                        @include('estudiantes._formulario')

                        <button type="submit" class="btn btn-success">Guardar cambios</button>
                        <a href="{{ route('estudiantes.index') }}" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
