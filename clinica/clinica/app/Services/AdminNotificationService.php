<?php

namespace App\Services;

use App\Mail\AdminAppointmentCreatedMail;
use App\Mail\AdminUserRegisteredMail;
use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AdminNotificationService
{
    public function notifyAppointmentCreated(Cita $cita): void
    {
        $cita->loadMissing(['paciente', 'servicio']);

        $this->admins()->each(function (User $admin) use ($cita): void {
            Mail::to($admin->email)->send(new AdminAppointmentCreatedMail($cita));

            NotificacionLog::create([
                'cita_id' => $cita->id,
                'canal' => 'email',
                'tipo' => 'admin_cita_creada',
                'destinatario' => $admin->email,
                'estado' => 'enviado',
                'payload' => [
                    'admin_id' => $admin->id,
                    'paciente_id' => $cita->paciente_id,
                    'paciente' => $cita->paciente?->nombre_completo,
                    'fecha' => $cita->fecha?->toDateString(),
                    'hora' => substr((string) $cita->hora, 0, 5),
                    'servicio' => $cita->servicio?->nombre,
                ],
                'enviado_en' => now(),
            ]);
        });
    }

    public function notifyUserRegistered(User $user): void
    {
        $user->loadMissing('paciente');

        $this->admins()->each(function (User $admin) use ($user): void {
            Mail::to($admin->email)->send(new AdminUserRegisteredMail($user));

            NotificacionLog::create([
                'canal' => 'email',
                'tipo' => 'admin_usuario_registrado',
                'destinatario' => $admin->email,
                'estado' => 'enviado',
                'payload' => [
                    'admin_id' => $admin->id,
                    'user_id' => $user->id,
                    'paciente_id' => $user->paciente?->id,
                    'nombre' => $user->name,
                    'correo' => $user->email,
                ],
                'enviado_en' => now(),
            ]);
        });
    }

    protected function admins()
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->whereNotNull('email')
            ->get();
    }
}
