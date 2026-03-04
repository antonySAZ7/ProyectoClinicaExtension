<!DOCTYPE html>
<html>
<head>
    <title>Registrar Paciente</title>
</head>
<body>

<h2>Registrar Paciente</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('pacientes.store') }}">
    @csrf

    <label>Nombre completo:</label><br>
    <input type="text" name="nombre_completo"><br><br>

    <label>DPI:</label><br>
    <input type="text" name="dpi"><br><br>

    <label>Fecha de nacimiento:</label><br>
    <input type="date" name="fecha_nacimiento"><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono"><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo"><br><br>

    <label>Dirección:</label><br>
    <input type="text" name="direccion"><br><br>

    <label>Sexo:</label><br>
    <input type="text" name="sexo"><br><br>

    <label>Estado civil:</label><br>
    <input type="text" name="estado_civil"><br><br>

    <label>Ocupación:</label><br>
    <input type="text" name="ocupacion"><br><br>

    <button type="submit">Guardar</button>
</form>

<br>

<a href="{{ route('pacientes.index') }}">
    <button>Volver</button>
</a>

</body>
</html>