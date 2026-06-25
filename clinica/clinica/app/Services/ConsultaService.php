<?php

namespace App\Services;

use App\Mail\ConsultaCerradaMail;
use App\Models\Archivo;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\NotificacionLog;
use App\Models\Observacion;
use App\Models\Paciente;
use App\Models\PiezaDental;
use App\Models\TarifaTratamiento;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ConsultaService
{
    public function paginatedHistory(Paciente $paciente): LengthAwarePaginator
    {
        return $paciente->consultas()
            ->with('user')
            ->withCount('archivos')
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
    }

    public function findLinkedCita(Paciente $paciente, ?int $citaId): ?Cita
    {
        if (! $citaId) {
            return null;
        }

        return Cita::where('id', $citaId)
            ->where('paciente_id', $paciente->id)
            ->first();
    }

    /**
     * @param  array<int, UploadedFile>  $archivos
     */
    public function createConsulta(Paciente $paciente, User $user, array $data, array $archivos = []): Consulta
    {
        $citaId = $this->findLinkedCita($paciente, $data['cita_id'] ?? null)?->id;

        $consulta = DB::transaction(function () use ($data, $archivos, $paciente, $user, $citaId) {
            $consulta = $paciente->consultas()->create([
                'user_id' => $user->id,
                'cita_id' => $citaId,
                'fecha' => $data['fecha'],
                'motivo' => $data['motivo'],
                'diagnostico' => $data['diagnostico'],
                'peso' => $data['peso'] ?? null,
                'altura' => $data['altura'] ?? null,
                'presion_arterial' => $data['presion_arterial'] ?? null,
                'frecuencia_cardiaca' => $data['frecuencia_cardiaca'] ?? null,
                'frecuencia_respiratoria' => $data['frecuencia_respiratoria'] ?? null,
                'signos_otros' => $data['signos_otros'] ?? null,
            ]);

            if ($citaId) {
                Cita::where('id', $citaId)->update(['estado' => Cita::ESTADO_ATENDIDA]);
            }

            if (! empty($data['observaciones'])) {
                $consulta->observaciones()->create([
                    'descripcion' => $data['observaciones'],
                ]);
            }

            $this->storeFiles($consulta, $archivos, $paciente->id);

            return $consulta;
        });

        $this->sendConsultaCerradaNotification($consulta);

        return $consulta;
    }

    public function loadForShow(Consulta $consulta): Consulta
    {
        return $consulta->load([
            'paciente.user',
            'paciente.consultas.presupuestoItems',
            'paciente.pagos',
            'user',
            'observaciones',
            'archivos',
            'presupuestoItems.pieza',
            'pagos',
            'consultaOrigen',
            'piezasDentales',
        ]);
    }

    public function piezasCatalogoFor(Consulta $consulta)
    {
        $estadosOdontograma = $consulta->piezasDentales
            ->mapWithKeys(fn ($p) => [$p->id => $p->pivot?->estado ?? 'sana']);

        return PiezaDental::query()
            ->orderBy('cuadrante')
            ->orderBy('posicion')
            ->get(['id', 'numero', 'nombre', 'cuadrante', 'posicion', 'tipo'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'numero' => $p->numeroVisible(),
                'numero_fdi' => $p->numero,
                'numero_referencia' => $p->numeroReferencia(),
                'nombre' => $p->nombre,
                'cuadrante' => $p->cuadrante,
                'posicion' => $p->posicion,
                'tipo' => $p->tipo,
                'tipo_legible' => $p->tipoLegible(),
                'estado_consulta' => $estadosOdontograma[$p->id] ?? null,
            ])
            ->values();
    }

    public function tipoOdontogramaInicial(Consulta $consulta): string
    {
        $edad = $consulta->paciente?->edad;

        return $edad !== null && $edad < 13
            ? PiezaDental::TIPO_TEMPORAL
            : PiezaDental::TIPO_PERMANENTE;
    }

    public function tarifasCatalogo()
    {
        return TarifaTratamiento::query()
            ->where('activo', true)
            ->orderBy('estado_pieza')
            ->get(['estado_pieza', 'nombre_legible', 'precio_sugerido']);
    }

    public function createFollowUp(Consulta $consulta, User $user): Consulta
    {
        $consulta->load(['paciente', 'piezasDentales']);

        return DB::transaction(function () use ($consulta, $user) {
            $seguimiento = $consulta->paciente->consultas()->create([
                'user_id' => $user->id,
                'consulta_origen_id' => $consulta->id,
                'fecha' => today()->toDateString(),
                'motivo' => 'Seguimiento de '.$consulta->fecha?->format('d/m/Y'),
                'diagnostico' => 'Seguimiento pendiente de documentar.',
            ]);

            $piezas = $consulta->piezasDentales
                ->mapWithKeys(fn ($pieza) => [
                    $pieza->id => [
                        'estado' => $pieza->pivot?->estado ?? 'sana',
                        'observaciones' => $pieza->pivot?->observaciones,
                    ],
                ])
                ->all();

            if (! empty($piezas)) {
                $seguimiento->piezasDentales()->sync($piezas);
            }

            return $seguimiento;
        });
    }

    public function addObservation(Consulta $consulta, array $data): Observacion
    {
        return $consulta->observaciones()->create($data);
    }

    public function deleteObservation(Observacion $observacion): int
    {
        $consultaId = $observacion->consulta_id;
        $observacion->delete();

        return $consultaId;
    }

    /**
     * @param  array<int, UploadedFile>  $archivos
     */
    public function addFiles(Consulta $consulta, array $archivos): void
    {
        $this->storeFiles($consulta, $archivos, $consulta->paciente_id);
    }

    public function deleteFile(Archivo $archivo): int
    {
        $consultaId = $archivo->consulta_id;

        if ($archivo->ruta && Storage::disk('public')->exists($archivo->ruta)) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        $archivo->delete();

        return $consultaId;
    }

    /**
     * @param  array<int, UploadedFile>  $archivos
     */
    protected function storeFiles(Consulta $consulta, array $archivos, int $pacienteId): void
    {
        foreach ($archivos as $archivoSubido) {
            $ruta = $archivoSubido->store("consultas/{$pacienteId}", 'public');

            $consulta->archivos()->create([
                'ruta' => $ruta,
                'tipo' => $archivoSubido->getClientMimeType() ?: $archivoSubido->extension(),
                'nombre_original' => $archivoSubido->getClientOriginalName(),
            ]);
        }
    }

    protected function sendConsultaCerradaNotification(Consulta $consulta): void
    {
        $consulta->loadMissing([
            'paciente.consultas.presupuestoItems',
            'paciente.pagos',
            'observaciones',
        ]);

        if (! $consulta->paciente?->correo) {
            return;
        }

        Mail::to($consulta->paciente->correo)->send(new ConsultaCerradaMail($consulta));

        NotificacionLog::create([
            'cita_id' => $consulta->cita_id,
            'canal' => 'email',
            'tipo' => 'consulta_cerrada',
            'destinatario' => $consulta->paciente->correo,
            'estado' => 'enviado',
            'payload' => [
                'consulta_id' => $consulta->id,
                'fecha' => $consulta->fecha?->toDateString(),
                'motivo' => $consulta->motivo,
                'saldo_actual' => $consulta->paciente->saldo_pendiente,
            ],
            'enviado_en' => now(),
        ]);
    }
}
