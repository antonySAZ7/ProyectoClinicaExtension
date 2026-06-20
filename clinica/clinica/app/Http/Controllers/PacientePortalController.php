<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use App\Services\CitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PacientePortalController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $user->load('paciente');

        $paciente = $user->paciente;
        $citas = collect();
        $consultasRecientes = collect();

        if ($paciente) {
            $paciente->loadMissing(['antecedenteClinico', 'consultas.presupuestoItems', 'pagos']);

            $citas = $paciente->citas()
                ->upcoming()
                ->orderBy('fecha')
                ->orderBy('hora')
                ->get();

            $consultasRecientes = $paciente->consultas()
                ->with('user')
                ->orderByDesc('fecha')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        return view('paciente.portal', [
            'user' => $user,
            'paciente' => $paciente,
            'citas' => $citas,
            'consultasRecientes' => $consultasRecientes,
        ]);
    }

    public function cancel(Request $request, Cita $cita, CitaService $service): RedirectResponse
    {
        [$type, $message] = $service->cancelForPatient($request->user(), $cita);

        return redirect()->route('portal')
            ->with($type, $message);
    }

    public function reschedule(
        Request $request,
        Cita $cita,
        CitaService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'servicio_id' => ['nullable', Rule::exists('servicios', 'id')->where('activo', true)],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
        ]);

        [$type, $message] = $service->rescheduleForPatient($request->user(), $cita, $validated);

        return redirect()->route('portal')
            ->with($type, $message);
    }
}
