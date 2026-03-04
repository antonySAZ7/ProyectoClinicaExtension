@extends('layouts.app')

@section('content')

<h2 class="mb-3">Lista de Pacientes</h2>

<form method="GET" action="{{ route('pacientes.index') }}" class="mb-3 d-flex">
    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por nombre o DPI">
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<a href="{{ route('pacientes.create') }}" class="btn btn-success mb-3">
    Nuevo Paciente
</a>

<table class="table table-bordered table-striped">
    <thead class="table-primary">
        <tr>
            <th>Nombre</th>
            <th>DPI</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pacientes as $paciente)
        <tr>
            <td>{{ $paciente->nombre_completo }}</td>
            <td>{{ $paciente->dpi }}</td>
            <td>{{ $paciente->telefono }}</td>
            <td>{{ $paciente->correo }}</td>
            <td>
                <a href="{{ route('pacientes.edit', $paciente->id) }}" class="btn btn-warning btn-sm">
                    Editar
                </a>

                <form action="{{ route('pacientes.destroy', $paciente->id) }}"
                      method="POST"
                      style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Eliminar paciente?')">
                        Eliminar
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection