<?php

namespace App\Services;

use App\Mail\CitaConfirmationMail;
use App\Models\Cita;
use App\Models\NotificacionLog;
use App\Models\Paciente;
use App\Models\RecordatorioSeguimiento;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CitaService
{
    public function __construct(
        protected AppointmentAvailabilityService $availability,
        protected AdminNotificationService $adminNotifications,
    ) {}

    public function createBackoffice(array $data): Cita
    {
        $followUpData = $this->extractFollowUpData($data);
        $data['estado'] = $data['estado'] ?? Cita::ESTADO_PENDIENTE;

        $this->validateFollowUpSelection($followUpData);
        $this->ensureAvailable($data['fecha'], $data['hora'], $data['hora_fin']);

        $cita = Cita::create($data);
        $this->syncFollowUpReminder($cita, $followUpData);
        $this->adminNotifications->notifyAppointmentCreated($cita);

        return $cita;
    }

    public function updateBackoffice(Cita $cita, array $data): Cita
    {
        $followUpData = $this->extractFollowUpData($data);
        $this->validateFollowUpSelection($followUpData);

        if (
            ! in_array($data['estado'], [Cita::ESTADO_CANCELADA, Cita::ESTADO_NO_SHOW], true)
            && ! $this->availability->isAvailable($data['fecha'], $data['hora'], $data['hora_fin'], $cita->id)
        ) {
            throw ValidationException::withMessages([
                'hora' => 'El horario seleccionado no está disponible.',
            ]);
        }

        $cita->update($data);
        $this->syncFollowUpReminder($cita, $followUpData);

        return $cita->refresh();
    }

    /**
     * @return array{0: Cita, 1: User|null, 2: User|null}
     */
    public function createPublicAppointment(array $data, ?User $user): array
    {
        $servicio = Servicio::findOrFail($data['servicio_id']);
        $horaFin = $this->availability->endTimeFor($data['hora'], $servicio);

        $this->ensureAvailable($data['fecha'], $data['hora'], $horaFin);

        [$cita, $pacienteUser, $createdUser] = DB::transaction(function () use ($data, $user, $servicio, $horaFin) {
            [$paciente, $createdUser] = $this->resolvePaciente($data, $user);
            $paciente->loadMissing('user');

            $cita = Cita::create([
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicio->id,
                'fecha' => $data['fecha'],
                'hora' => $data['hora'],
                'hora_fin' => $horaFin,
                'motivo' => ($data['motivo'] ?? null) ?: $servicio->nombre,
                'estado' => Cita::ESTADO_PENDIENTE,
            ]);

            return [$cita, $paciente->user, $createdUser];
        });

        $cita->load(['paciente', 'servicio']);
        Mail::to($cita->paciente->correo)->send(new CitaConfirmationMail($cita));
        $this->logEmail($cita, 'confirmacion_agendamiento', $cita->paciente->correo, [
            'fecha' => $cita->fecha?->toDateString(),
            'hora' => substr((string) $cita->hora, 0, 5),
            'servicio' => $cita->servicio?->nombre,
        ]);

        $this->adminNotifications->notifyAppointmentCreated($cita);

        if ($createdUser) {
            $this->adminNotifications->notifyUserRegistered($createdUser);
        }

        return [$cita, $pacienteUser, $createdUser];
    }

    public function confirmForPatient(User $user, Cita $cita): array
    {
        $user->loadMissing('paciente');

        if (! $user->paciente || $cita->paciente_id !== $user->paciente->id) {
            abort(403);
        }

        if (! $cita->isFuture()) {
            return ['error', 'No puedes confirmar una cita pasada.'];
        }

        if ($cita->estado === Cita::ESTADO_CANCELADA) {
            return ['error', 'No puedes confirmar una cita cancelada.'];
        }

        if ($cita->estado === Cita::ESTADO_CONFIRMADA) {
            return ['success', 'Tu cita ya estaba confirmada.'];
        }

        $cita->update(['estado' => Cita::ESTADO_CONFIRMADA]);

        return ['success', 'Tu cita fue confirmada correctamente.'];
    }

    public function cancelBackoffice(Cita $cita): Cita
    {
        $cita->update(['estado' => Cita::ESTADO_CANCELADA]);

        return $cita->refresh();
    }

    public function cancelForPatient(User $user, Cita $cita): array
    {
        $paciente = $this->patientFor($user);
        if (! $paciente) {
            return ['error', 'Tu usuario todavía no tiene un expediente de paciente asociado.'];
        }

        $cita = $paciente->citas()
            ->upcoming()
            ->findOrFail($cita->getKey());

        if ($cita->estado === Cita::ESTADO_CANCELADA) {
            return ['error', 'La cita seleccionada ya estaba cancelada.'];
        }

        $cita->update(['estado' => Cita::ESTADO_CANCELADA]);

        return ['success', 'La cita fue cancelada correctamente.'];
    }

    public function rescheduleForPatient(User $user, Cita $cita, array $data): array
    {
        $paciente = $this->patientFor($user);
        if (! $paciente) {
            return ['error', 'Tu usuario todavía no tiene un expediente de paciente asociado.'];
        }

        $cita = $paciente->citas()
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
            ->findOrFail($cita->getKey());

        if (! $cita->isFuture()) {
            return ['error', 'No puedes reagendar una cita pasada.'];
        }

        $servicio = isset($data['servicio_id'])
            ? Servicio::find($data['servicio_id'])
            : $cita->servicio;

        $horaFin = $servicio
            ? $this->availability->endTimeFor($data['hora'], $servicio)
            : now()->setTimeFromTimeString($data['hora'])->addMinutes(30)->format('H:i');

        if (! $this->availability->isAvailable($data['fecha'], $data['hora'], $horaFin, $cita->id)) {
            return ['error', 'El nuevo horario seleccionado no está disponible.'];
        }

        $cita->update([
            'servicio_id' => $servicio?->id,
            'fecha' => $data['fecha'],
            'hora' => $data['hora'],
            'hora_fin' => $horaFin,
            'estado' => Cita::ESTADO_PENDIENTE,
        ]);

        $this->logEmail($cita, 'reagendamiento_paciente', $paciente->correo, [
            'fecha' => $cita->fecha?->toDateString(),
            'hora' => substr((string) $cita->hora, 0, 5),
            'hora_fin' => substr((string) $cita->hora_fin, 0, 5),
        ], 'registrado');

        return ['success', 'Tu cita fue reagendada correctamente.'];
    }

    public function markAttended(Cita $cita): Cita
    {
        $cita->update(['estado' => Cita::ESTADO_ATENDIDA]);

        return $cita->refresh();
    }

    public function closeExpiredNoShows(): int
    {
        return Cita::query()
            ->whereDate('fecha', '<', today()->toDateString())
            ->whereIn('estado', [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA])
            ->whereDoesntHave('consulta')
            ->update(['estado' => Cita::ESTADO_NO_SHOW]);
    }

    protected function ensureAvailable(string $fecha, string $hora, string $horaFin, ?int $exceptCitaId = null): void
    {
        if (! $this->availability->isAvailable($fecha, $hora, $horaFin, $exceptCitaId)) {
            throw ValidationException::withMessages([
                'hora' => 'El horario seleccionado no está disponible.',
            ]);
        }
    }

    protected function extractFollowUpData(array &$data): array
    {
        $followUp = [
            'activo' => (bool) ($data['activar_recordatorio_seguimiento'] ?? false),
            'modo' => $data['recordatorio_modo'] ?? RecordatorioSeguimiento::MODO_INTERVALO,
            'titulo' => $data['recordatorio_titulo'] ?? null,
            'intervalo_meses' => $data['recordatorio_intervalo_meses'] ?? null,
            'fecha_objetivo' => $data['recordatorio_fecha_objetivo'] ?? null,
            'dias_antes' => $data['recordatorio_dias_antes'] ?? [7, 1, 0],
            'mensaje' => $data['recordatorio_mensaje'] ?? null,
        ];

        unset(
            $data['activar_recordatorio_seguimiento'],
            $data['recordatorio_modo'],
            $data['recordatorio_titulo'],
            $data['recordatorio_intervalo_meses'],
            $data['recordatorio_fecha_objetivo'],
            $data['recordatorio_dias_antes'],
            $data['recordatorio_mensaje']
        );

        return $followUp;
    }

    protected function syncFollowUpReminder(Cita $cita, array $data): void
    {
        if (! $data['activo']) {
            $cita->recordatoriosSeguimiento()->delete();

            return;
        }

        $modo = $data['modo'];
        $intervaloMeses = $modo === RecordatorioSeguimiento::MODO_INTERVALO
            ? (int) ($data['intervalo_meses'] ?: 6)
            : null;

        $fechaObjetivo = $modo === RecordatorioSeguimiento::MODO_INTERVALO
            ? $cita->fecha?->copy()->addMonthsNoOverflow($intervaloMeses)
            : $data['fecha_objetivo'];

        if (! $fechaObjetivo) {
            return;
        }

        $cita->recordatoriosSeguimiento()->delete();
        $cita->recordatoriosSeguimiento()->create([
            'paciente_id' => $cita->paciente_id,
            'activo' => true,
            'modo' => $modo,
            'titulo' => $data['titulo'],
            'intervalo_meses' => $intervaloMeses,
            'fecha_objetivo' => $fechaObjetivo,
            'dias_antes' => collect($data['dias_antes'] ?: [7, 1, 0])
                ->map(fn ($day) => (int) $day)
                ->unique()
                ->values()
                ->all(),
            'mensaje' => $data['mensaje'],
            'fechas_enviadas' => [],
        ]);
    }

    protected function validateFollowUpSelection(array $data): void
    {
        if (
            $data['activo']
            && $data['modo'] === RecordatorioSeguimiento::MODO_PERSONALIZADO
            && empty($data['fecha_objetivo'])
        ) {
            throw ValidationException::withMessages([
                'recordatorio_fecha_objetivo' => 'Selecciona la fecha objetivo del recordatorio.',
            ]);
        }
    }

    /**
     * @return array{0: Paciente, 1: User|null}
     */
    protected function resolvePaciente(array $data, ?User $user): array
    {
        if ($user?->isPaciente()) {
            $user->loadMissing('paciente');

            if ($user->paciente) {
                return [$user->paciente, null];
            }
        }

        $createdUser = null;

        if (! $user || ! $user->isPaciente()) {
            $createdUser = $user = User::create([
                'name' => $data['nombre_completo'],
                'email' => $data['correo'],
                'role' => User::ROLE_PACIENTE,
                'password' => Hash::make($data['password']),
            ]);
        }

        $paciente = Paciente::create([
            'user_id' => $user->id,
            'nombre_completo' => $data['nombre_completo'] ?? $user->name,
            'dpi' => $data['dpi'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'telefono' => $data['telefono'],
            'correo' => $data['correo'] ?? $user->email,
            'direccion' => $data['direccion'],
        ]);

        return [$paciente, $createdUser];
    }

    protected function patientFor(User $user): ?Paciente
    {
        $user->loadMissing('paciente');

        return $user->paciente;
    }

    protected function logEmail(Cita $cita, string $tipo, ?string $to, array $payload, string $estado = 'enviado'): void
    {
        NotificacionLog::create([
            'cita_id' => $cita->id,
            'canal' => 'email',
            'tipo' => $tipo,
            'destinatario' => $to,
            'estado' => $estado,
            'payload' => $payload,
            'enviado_en' => now(),
        ]);
    }
}
