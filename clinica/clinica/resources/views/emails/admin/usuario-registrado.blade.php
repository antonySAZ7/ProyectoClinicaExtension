<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo usuario registrado</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h1 style="font-size: 20px; margin-bottom: 12px;">Nuevo usuario registrado</h1>

    <p>Se registro un nuevo usuario en DENS32.</p>

    <ul>
        <li><strong>Nombre:</strong> {{ $registeredUser->name }}</li>
        <li><strong>Correo:</strong> {{ $registeredUser->email }}</li>
        <li><strong>Rol:</strong> {{ ucfirst((string) $registeredUser->role) }}</li>
        @if ($registeredUser->paciente)
            <li><strong>DPI:</strong> {{ $registeredUser->paciente->dpi }}</li>
            <li><strong>Telefono:</strong> {{ $registeredUser->paciente->telefono }}</li>
        @endif
    </ul>

    <p>Ingresa al panel administrativo para revisar el expediente del paciente.</p>
</body>
</html>
