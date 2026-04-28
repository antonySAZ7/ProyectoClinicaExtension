<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoController extends Controller
{
    public function ver(Request $request, Archivo $archivo): StreamedResponse
    {
        $this->authorizeArchivoAccess($request->user(), $archivo);

        abort_unless(Storage::disk('public')->exists($archivo->ruta), 404);

        return Storage::disk('public')->response(
            $archivo->ruta,
            $archivo->nombre_original ?? basename($archivo->ruta)
        );
    }

    public function descargar(Request $request, Archivo $archivo): StreamedResponse
    {
        $this->authorizeArchivoAccess($request->user(), $archivo);

        abort_unless(Storage::disk('public')->exists($archivo->ruta), 404);

        return Storage::disk('public')->download(
            $archivo->ruta,
            $archivo->nombre_original ?? basename($archivo->ruta)
        );
    }

    protected function authorizeArchivoAccess(User $user, Archivo $archivo): void
    {
        $archivo->loadMissing('consulta');

        abort_unless($archivo->consulta, 404);

        if ($user->isPaciente()) {
            $user->loadMissing('paciente');

            if (! $user->paciente || $archivo->consulta->paciente_id !== $user->paciente->id) {
                abort(403);
            }

            return;
        }

        if (! $user->canManageClinicalHistory()) {
            abort(403);
        }
    }
}
