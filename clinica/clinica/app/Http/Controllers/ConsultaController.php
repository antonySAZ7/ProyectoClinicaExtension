<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Http\Requests\UpdateConsultaRequest;
use App\Models\Archivo;
use App\Models\Cita;
use App\Models\Consulta;
use App\Models\Observacion;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ConsultaController extends Controller
{
    public function index(Paciente $paciente): View
    {
        $consultas = $paciente->consultas()
            ->with(['user', 'observaciones', 'archivos'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        return view('consultas.index', [
            'paciente' => $paciente,
            'consultas' => $consultas,
            'isPortal' => false,
        ]);
    }

    public function portalIndex(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->load('paciente');

        if (! $user->paciente) {
            return redirect()->route('portal')
                ->with('error', 'Tu usuario todavia no tiene un expediente de paciente asociado.');
        }

        $paciente = $user->paciente;
        $consultas = $paciente->consultas()
            ->with(['user', 'observaciones', 'archivos'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        return view('consultas.index', [
            'paciente' => $paciente,
            'consultas' => $consultas,
            'isPortal' => true,
        ]);
    }

    public function create(Request $request, Paciente $paciente): View
    {
        $cita = null;
        if ($request->filled('cita_id')) {
            $cita = Cita::where('id', $request->integer('cita_id'))
                ->where('paciente_id', $paciente->id)
                ->first();
        }

        return view('consultas.create', [
            'paciente' => $paciente,
            'cita' => $cita,
            'observaciones' => old('observaciones', ''),
        ]);
    }

    public function store(StoreConsultaRequest $request, Paciente $paciente): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $citaId = null;
        if (! empty($validated['cita_id'])) {
            $cita = Cita::where('id', $validated['cita_id'])
                ->where('paciente_id', $paciente->id)
                ->first();
            $citaId = $cita?->id;
        }

        $consulta = DB::transaction(function () use ($validated, $request, $paciente, $user, $citaId) {
            $consulta = $paciente->consultas()->create([
                'user_id' => $user->id,
                'cita_id' => $citaId,
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'],
                'diagnostico' => $validated['diagnostico'],
                'peso' => $validated['peso'] ?? null,
                'altura' => $validated['altura'] ?? null,
                'presion_arterial' => $validated['presion_arterial'] ?? null,
                'frecuencia_cardiaca' => $validated['frecuencia_cardiaca'] ?? null,
                'frecuencia_respiratoria' => $validated['frecuencia_respiratoria'] ?? null,
                'signos_otros' => $validated['signos_otros'] ?? null,
            ]);

            if ($citaId) {
                Cita::where('id', $citaId)->update(['estado' => Cita::ESTADO_ATENDIDA]);
            }

            if (! empty($validated['observaciones'])) {
                $consulta->observaciones()->create([
                    'descripcion' => $validated['observaciones'],
                ]);
            }

            foreach ($request->file('archivos', []) as $archivoSubido) {
                $ruta = $archivoSubido->store("consultas/{$paciente->id}", 'public');

                $consulta->archivos()->create([
                    'ruta' => $ruta,
                    'tipo' => $archivoSubido->getClientMimeType() ?: $archivoSubido->extension(),
                    'nombre_original' => $archivoSubido->getClientOriginalName(),
                ]);
            }

            return $consulta;
        });

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Consulta registrada correctamente.');
    }

    public function show(Request $request, Consulta $consulta): View
    {
        /** @var User $user */
        $user = $request->user();

        $consulta->load(['paciente.user', 'user', 'observaciones', 'archivos']);

        $this->authorizeConsultaView($user, $consulta);

        return view('consultas.show', [
            'consulta' => $consulta,
            'isPortal' => $user->isPaciente(),
        ]);
    }

    public function edit(Consulta $consulta): View
    {
        $consulta->load('paciente');

        return view('consultas.edit', [
            'consulta' => $consulta,
            'paciente' => $consulta->paciente,
        ]);
    }

    public function update(UpdateConsultaRequest $request, Consulta $consulta): RedirectResponse
    {
        $consulta->update($request->validated());

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Consulta actualizada correctamente.');
    }

    public function storeObservacion(Request $request, Consulta $consulta): RedirectResponse
    {
        if (! $request->user()?->canManageClinicalHistory()) {
            abort(403);
        }

        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:4000'],
        ]);

        $consulta->observaciones()->create($validated);

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Observación agregada.');
    }

    public function destroyObservacion(Request $request, Observacion $observacion): RedirectResponse
    {
        if (! $request->user()?->canManageClinicalHistory()) {
            abort(403);
        }

        $consultaId = $observacion->consulta_id;
        $observacion->delete();

        return redirect()->route('consultas.show', $consultaId)
            ->with('success', 'Observación eliminada.');
    }

    public function storeArchivo(Request $request, Consulta $consulta): RedirectResponse
    {
        if (! $request->user()?->canManageClinicalHistory()) {
            abort(403);
        }

        $request->validate([
            'archivos' => ['required', 'array'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach ($request->file('archivos', []) as $archivoSubido) {
            $ruta = $archivoSubido->store("consultas/{$consulta->paciente_id}", 'public');

            $consulta->archivos()->create([
                'ruta' => $ruta,
                'tipo' => $archivoSubido->getClientMimeType() ?: $archivoSubido->extension(),
                'nombre_original' => $archivoSubido->getClientOriginalName(),
            ]);
        }

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Archivo(s) subido(s) correctamente.');
    }

    public function destroyArchivo(Request $request, Archivo $archivo): RedirectResponse
    {
        if (! $request->user()?->canManageClinicalHistory()) {
            abort(403);
        }

        $consultaId = $archivo->consulta_id;

        if ($archivo->ruta && Storage::disk('public')->exists($archivo->ruta)) {
            Storage::disk('public')->delete($archivo->ruta);
        }

        $archivo->delete();

        return redirect()->route('consultas.show', $consultaId)
            ->with('success', 'Archivo eliminado.');
    }

    protected function authorizeConsultaView(User $user, Consulta $consulta): void
    {
        if ($user->isPaciente()) {
            $user->loadMissing('paciente');

            if (! $user->paciente || $consulta->paciente_id !== $user->paciente->id) {
                abort(403);
            }

            return;
        }

        if (! $user->canManageClinicalHistory()) {
            abort(403);
        }
    }
}
