@extends('layouts.app')

@section('content')

<h2 class="mb-4">Editar Paciente</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('pacientes.update', $paciente->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Nombre completo</label>
        <input type="text" 
               name="nombre_completo" 
               class="form-control"
               value="{{ old('nombre_completo', $paciente->nombre_completo) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">DPI</label>
        <input type="text" 
               name="dpi" 
               class="form-control"
               value="{{ old('dpi', $paciente->dpi) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" 
               name="fecha_nacimiento" 
               class="form-control"
               value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Teléfono</label>
        <input type="text" 
               name="telefono" 
               class="form-control"
               value="{{ old('telefono', $paciente->telefono) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="email" 
               name="correo" 
               class="form-control"
               value="{{ old('correo', $paciente->correo) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Dirección</label>
        <input type="text" 
               name="direccion" 
               class="form-control"
               value="{{ old('direccion', $paciente->direccion) }}">
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            Actualizar
        </button>

        <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

</form>

@endsection