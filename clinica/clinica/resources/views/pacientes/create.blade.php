@extends('layouts.app')

@section('content')

<h2 class="mb-4">Registrar Paciente</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('pacientes.store') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">Nombre completo</label>
        <input type="text" name="nombre_completo" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">DPI</label>
        <input type="text" name="dpi" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">
        Guardar
    </button>

    <a href="{{ route('pacientes.index') }}" class="btn btn-secondary">
        Volver
    </a>
</form>

@endsection