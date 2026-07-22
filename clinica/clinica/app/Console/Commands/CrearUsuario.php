<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Crea un usuario de staff (doctor o administrador).
 *
 * Es la vía oficial para dar de alta personal de la clínica: el registro
 * público solo crea pacientes, así que doctores y admins se provisionan
 * por aquí (o con el seeder inicial vía SEED_ADMIN_*). Los pacientes NO se
 * crean con este comando; ellos se registran solos.
 *
 * Uso interactivo:
 *   php artisan clinica:crear-usuario
 *
 * Uso con banderas (para automatización / scripts de despliegue):
 *   php artisan clinica:crear-usuario --name="Dra. Fulana" --email=dra@clinica.com --role=doctor --password=Secreto123
 */
class CrearUsuario extends Command
{
    protected $signature = 'clinica:crear-usuario
        {--name= : Nombre completo del usuario}
        {--email= : Correo (debe ser único)}
        {--role= : Rol: admin o doctor}
        {--password= : Contraseña (si se omite, se pide de forma interactiva)}';

    protected $description = 'Crea un usuario de staff (doctor o administrador).';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Nombre completo');
        $email = $this->option('email') ?: $this->ask('Correo electrónico');

        $role = $this->option('role') ?: $this->choice(
            'Rol',
            [User::ROLE_ADMIN, User::ROLE_DOCTOR],
            User::ROLE_DOCTOR
        );

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Contraseña');
            $confirmacion = $this->secret('Confirmar contraseña');

            if ($password !== $confirmacion) {
                $this->error('Las contraseñas no coinciden.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password' => $password,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
                'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_DOCTOR])],
                'password' => ['required', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Usuario creado: {$user->name} <{$user->email}> con rol {$user->role}.");

        return self::SUCCESS;
    }
}
