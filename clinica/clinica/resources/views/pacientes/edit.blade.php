<h2>Editar Paciente</h2>

@if ($errors->any())
    <div style="color:red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('pacientes.update', $paciente->id) }}">
    @csrf
    @method('PUT')

    <input type="text" name="nombre_completo" value="{{ $paciente->nombre_completo }}"><br><br>
    <input type="text" name="dpi" value="{{ $paciente->dpi }}"><br><br>
    <input type="date" name="fecha_nacimiento" value="{{ $paciente->fecha_nacimiento }}"><br><br>
    <input type="text" name="telefono" value="{{ $paciente->telefono }}"><br><br>
    <input type="email" name="correo" value="{{ $paciente->correo }}"><br><br>
    <input type="text" name="direccion" value="{{ $paciente->direccion }}"><br><br>
    <input type="text" name="sexo" value="{{ $paciente->sexo }}"><br><br>
    <input type="text" name="estado_civil" value="{{ $paciente->estado_civil }}"><br><br>
    <input type="text" name="ocupacion" value="{{ $paciente->ocupacion }}"><br><br>

    <button type="submit">Actualizar</button>
</form>

<a href="{{ route('pacientes.index') }}">
    <button>Volver</button>
</a>