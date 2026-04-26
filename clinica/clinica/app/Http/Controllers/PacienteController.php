<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buscar = request('buscar');

        $pacientes = Paciente::with('user')->when($buscar, function (Builder $query, string $buscar) {
            return $query->where('nombre_completo', 'like', "%$buscar%")
                ->orWhere('dpi', 'like', "%$buscar%");
        })->get();

        return view('pacientes.index', compact('pacientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pacientes.create', [
            'usuariosPaciente' => $this->usuariosPacienteDisponibles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePacienteRequest $request)
    {
        Paciente::create($request->validated());

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Paciente $paciente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paciente $paciente)
    {
        return view('pacientes.edit', [
            'paciente' => $paciente,
            'usuariosPaciente' => $this->usuariosPacienteDisponibles($paciente),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePacienteRequest $request, Paciente $paciente)
    {
        $paciente->update($request->validated());

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }

    protected function usuariosPacienteDisponibles(?Paciente $paciente = null)
    {
        return User::query()
            ->where('role', User::ROLE_PACIENTE)
            ->where(function (Builder $query) use ($paciente) {
                $query->whereDoesntHave('paciente');

                if ($paciente && $paciente->user_id) {
                    $query->orWhere('id', $paciente->user_id);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
