<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PacientePortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'paciente.citas' => fn ($query) => $query
                ->upcoming()
                ->orderBy('fecha')
                ->orderBy('hora'),
        ]);

        return view('portal.index', [
            'user' => $user,
            'paciente' => $user->paciente,
        ]);
    }
}
