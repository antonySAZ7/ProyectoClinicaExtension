<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Pacientes</title>
</head>
<body>

<h2>Lista de Pacientes</h2>

@if(session('success'))
    <p style="color: green;">
        {{ session('success') }}
    </p>
@endif

<form method="GET" action="{{ route('pacientes.index') }}">
    <input type="text" name="buscar" placeholder="Buscar por nombre o DPI">
    <button type="submit">Buscar</button>
</form>

<br>

<a href="{{ route('pacientes.create') }}">
    <button>Nuevo Paciente</button>
</a>

<br><br>

<table class="table table-bordered">
    <tr>
        <th>Nombre</th>
        <th>DPI</th>
        <th>Teléfono</th>
        <th>Correo</th>
    </tr>

    @foreach($pacientes as $paciente)
    <tr>
        <td>{{ $paciente->nombre_completo }}</td>
        <td>{{ $paciente->dpi }}</td>
        <td>{{ $paciente->telefono }}</td>
        <td>{{ $paciente->correo }}</td>
        <td>
            <a href="{{ route('pacientes.edit', $paciente->id) }}">
                <button class="btn btn-warning btn-sm">Editar</button>
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

</table>

</body>
</html>