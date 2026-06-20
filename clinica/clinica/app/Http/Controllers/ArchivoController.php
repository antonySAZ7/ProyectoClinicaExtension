<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoController extends Controller
{
    public function ver(Request $request, Archivo $archivo): StreamedResponse
    {
        $archivo->loadMissing('consulta');
        abort_unless($archivo->consulta, 404);
        $this->authorize('view', $archivo->consulta);

        abort_unless(Storage::disk('public')->exists($archivo->ruta), 404);

        return Storage::disk('public')->response(
            $archivo->ruta,
            $archivo->nombre_original ?? basename($archivo->ruta)
        );
    }

    public function descargar(Request $request, Archivo $archivo): StreamedResponse
    {
        $archivo->loadMissing('consulta');
        abort_unless($archivo->consulta, 404);
        $this->authorize('view', $archivo->consulta);

        abort_unless(Storage::disk('public')->exists($archivo->ruta), 404);

        return Storage::disk('public')->download(
            $archivo->ruta,
            $archivo->nombre_original ?? basename($archivo->ruta)
        );
    }
}
