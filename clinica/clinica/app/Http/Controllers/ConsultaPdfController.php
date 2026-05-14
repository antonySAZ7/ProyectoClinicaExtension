<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\PiezaDental;
use Illuminate\Http\Response;

class ConsultaPdfController extends Controller
{
    public function __invoke(Consulta $consulta): Response
    {
        abort_unless(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class), 500, 'barryvdh/laravel-dompdf no esta instalado.');

        $consulta->load(['paciente', 'user', 'observaciones', 'archivos', 'piezasDentales']);

        $piezasConsulta = $consulta->piezasDentales->keyBy('id');
        $piezas = PiezaDental::query()
            ->orderBy('cuadrante')
            ->orderBy('posicion')
            ->get()
            ->map(function (PiezaDental $pieza) use ($piezasConsulta) {
                $registrada = $piezasConsulta->get($pieza->id);
                $pieza->estado_odontograma = $registrada?->pivot?->estado ?? 'sana';
                $pieza->observaciones_odontograma = $registrada?->pivot?->observaciones;

                return $pieza;
            });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('consultas.pdf', [
            'consulta' => $consulta,
            'piezas' => $piezas,
        ]);

        return $pdf->download("consulta-{$consulta->id}.pdf");
    }
}
