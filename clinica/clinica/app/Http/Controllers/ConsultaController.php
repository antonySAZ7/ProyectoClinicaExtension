<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaRequest;
use App\Http\Requests\UpdateConsultaRequest;
use App\Models\Archivo;
use App\Models\Consulta;
use App\Models\Observacion;
use App\Models\Paciente;
use App\Services\ConsultaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultaController extends Controller
{
    public function index(Paciente $paciente, ConsultaService $service): View
    {
        $this->authorize('viewAny', [Consulta::class, $paciente]);

        return view('consultas.index', [
            'paciente' => $paciente,
            'consultas' => $service->paginatedHistory($paciente),
            'isPortal' => false,
        ]);
    }

    public function portalIndex(Request $request, ConsultaService $service): View|RedirectResponse
    {
        $user = $request->user();

        $user->load('paciente');

        if (! $user->paciente) {
            return redirect()->route('portal')
                ->with('error', 'Tu usuario todavía no tiene un expediente de paciente asociado.');
        }

        $paciente = $user->paciente;

        return view('consultas.index', [
            'paciente' => $paciente,
            'consultas' => $service->paginatedHistory($paciente),
            'isPortal' => true,
        ]);
    }

    public function create(Request $request, Paciente $paciente, ConsultaService $service): View
    {
        $this->authorize('create', [Consulta::class, $paciente]);

        return view('consultas.create', [
            'paciente' => $paciente,
            'cita' => $service->findLinkedCita($paciente, $request->integer('cita_id') ?: null),
            'observaciones' => old('observaciones', ''),
        ]);
    }

    public function store(StoreConsultaRequest $request, Paciente $paciente, ConsultaService $service): RedirectResponse
    {
        $this->authorize('create', [Consulta::class, $paciente]);

        $consulta = $service->createConsulta(
            $paciente,
            $request->user(),
            $request->validated(),
            $request->file('archivos', [])
        );

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Consulta registrada correctamente.');
    }

    public function show(Request $request, Consulta $consulta, ConsultaService $service): View
    {
        $user = $request->user();
        $service->loadForShow($consulta);
        $this->authorize('view', $consulta);

        return view('consultas.show', [
            'consulta' => $consulta,
            'isPortal' => $user->isPaciente(),
            'piezasCatalogo' => $service->piezasCatalogoFor($consulta),
            'tarifasCatalogo' => $service->tarifasCatalogo(),
            'odontogramaTipoInicial' => $service->tipoOdontogramaInicial($consulta),
        ]);
    }

    public function edit(Consulta $consulta): View
    {
        $this->authorize('update', $consulta);
        $consulta->load('paciente');

        return view('consultas.edit', [
            'consulta' => $consulta,
            'paciente' => $consulta->paciente,
        ]);
    }

    public function update(UpdateConsultaRequest $request, Consulta $consulta): RedirectResponse
    {
        $this->authorize('update', $consulta);
        $consulta->update($request->validated());

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Consulta actualizada correctamente.');
    }

    public function storeFollowUp(Request $request, Consulta $consulta, ConsultaService $service): RedirectResponse
    {
        $this->authorize('manage', $consulta);
        $seguimiento = $service->createFollowUp($consulta, $request->user());

        return redirect()->route('consultas.show', $seguimiento)
            ->with('success', 'Consulta de seguimiento creada correctamente.');
    }

    public function storeObservacion(Request $request, Consulta $consulta, ConsultaService $service): RedirectResponse
    {
        $this->authorize('manage', $consulta);

        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:4000'],
        ]);

        $service->addObservation($consulta, $validated);

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Observación agregada.');
    }

    public function destroyObservacion(Observacion $observacion, ConsultaService $service): RedirectResponse
    {
        $observacion->loadMissing('consulta');
        $this->authorize('manage', $observacion->consulta);

        $consultaId = $service->deleteObservation($observacion);

        return redirect()->route('consultas.show', $consultaId)
            ->with('success', 'Observación eliminada.');
    }

    public function storeArchivo(Request $request, Consulta $consulta, ConsultaService $service): RedirectResponse
    {
        $this->authorize('manage', $consulta);

        $request->validate([
            'archivos' => ['required', 'array'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $service->addFiles($consulta, $request->file('archivos', []));

        return redirect()->route('consultas.show', $consulta)
            ->with('success', 'Archivo(s) subido(s) correctamente.');
    }

    public function destroyArchivo(Archivo $archivo, ConsultaService $service): RedirectResponse
    {
        $archivo->loadMissing('consulta');
        $this->authorize('manage', $archivo->consulta);
        $consultaId = $service->deleteFile($archivo);

        return redirect()->route('consultas.show', $consultaId)
            ->with('success', 'Archivo eliminado.');
    }
}
