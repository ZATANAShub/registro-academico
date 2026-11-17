{{-- Campos compartidos por "registrar" y "editar". $estudiante puede ser null al registrar. --}}
<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input id="nombre" type="text" name="nombre" maxlength="80" required
           value="{{ old('nombre', $estudiante?->nombre) }}"
           class="form-control @error('nombre') is-invalid @enderror">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="correo" class="form-label">Correo electrónico</label>
    <input id="correo" type="email" name="correo" maxlength="120" required
           value="{{ old('correo', $estudiante?->correo) }}"
           class="form-control @error('correo') is-invalid @enderror">
    @error('correo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="edad" class="form-label">Edad</label>
    <input id="edad" type="number" name="edad" min="16" max="100" required
           value="{{ old('edad', $estudiante?->edad) }}"
           class="form-control @error('edad') is-invalid @enderror">
    @error('edad')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="programa" class="form-label">Programa</label>
    <select id="programa" name="programa" required class="form-select @error('programa') is-invalid @enderror">
        <option value="">Selecciona un programa…</option>
        @foreach ($programas as $programa)
            <option value="{{ $programa }}" @selected(old('programa', $estudiante?->programa) === $programa)>
                {{ $programa }}
            </option>
        @endforeach
    </select>
    @error('programa')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
