<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function cancel(Request $request, Cita $cita): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $paciente = $user->paciente;

        if (! $paciente) {
            return redirect()->route('portal')
                ->with('error', 'Tu usuario todavia no tiene un expediente de paciente asociado.');
        }

        $cita = $paciente->citas()
            ->upcoming()
            ->findOrFail($cita->getKey());

        if ($cita->estado === 'cancelada') {
            return redirect()->route('portal')
                ->with('error', 'La cita seleccionada ya estaba cancelada.');
        }

        $cita->update([
            'estado' => 'cancelada',
        ]);

        return redirect()->route('portal')
            ->with('success', 'La cita fue cancelada correctamente.');
    }
}
