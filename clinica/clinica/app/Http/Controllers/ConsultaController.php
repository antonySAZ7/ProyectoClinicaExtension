<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function create(Paciente $paciente): View
    {
        return view('consultas.create', [
            'paciente' => $paciente,
            'observaciones' => old('observaciones', ''),
        ]);
    }

    public function store(StoreConsultaRequest $request, Paciente $paciente): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $consulta = DB::transaction(function () use ($validated, $request, $paciente, $user) {
            $consulta = $paciente->consultas()->create([
                'user_id' => $user->id,
                'fecha' => $validated['fecha'],
                'motivo' => $validated['motivo'],
                'diagnostico' => $validated['diagnostico'],
            ]);

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
