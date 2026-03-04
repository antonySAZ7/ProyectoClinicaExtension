<?php

namespace App\Http\Controllers;
use App\Models\Paciente;
use Illuminate\Http\Request;


class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buscar = request('buscar');

        $pacientes = Paciente::when($buscar, function ($query, $buscar) {
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
        return view('pacientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'nombre_completo'   => 'required|max:255',
        'dpi'               => 'required|unique:pacientes,dpi',
        'fecha_nacimiento'  => 'required|date',
        'telefono'          => 'required',
        'correo'            => 'required|email|unique:pacientes,correo',
        'direccion'         => 'required',
    ], [
        'nombre_completo.required'  => 'El nombre es obligatorio.',
        'dpi.required'              => 'El DPI es obligatorio.',
        'dpi.unique'                => 'El DPI ya está registrado.',
        'fecha_nacimiento.required' => 'Debe ingresar la fecha de nacimiento.',
        'fecha_nacimiento.date'     => 'La fecha ingresada no es válida.',
        'telefono.required'         => 'El teléfono es obligatorio.',
        'correo.required'           => 'El correo es obligatorio.',
        'correo.email'              => 'Debe ingresar un correo válido.',
        'correo.unique'             => 'El correo ya está registrado.',
        'direccion.required'        => 'La dirección es obligatoria.',
    ]);

    Paciente::create($request->all());

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
        
        return view('pacientes.edit', compact('paciente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paciente $paciente)
{
    $request->validate([
        'nombre_completo'   => 'required|max:255',
        'dpi'               => 'required|unique:pacientes,dpi,' . $paciente->id,
        'fecha_nacimiento'  => 'required|date',
        'telefono'          => 'required',
        'correo'            => 'required|email|unique:pacientes,correo,' . $paciente->id,
        'direccion'         => 'required',
    ], [
        'nombre_completo.required'  => 'El nombre es obligatorio.',
        'dpi.required'              => 'El DPI es obligatorio.',
        'dpi.unique'                => 'El DPI ya está registrado.',
        'fecha_nacimiento.required' => 'Debe ingresar la fecha de nacimiento.',
        'fecha_nacimiento.date'     => 'La fecha ingresada no es válida.',
        'telefono.required'         => 'El teléfono es obligatorio.',
        'correo.required'           => 'El correo es obligatorio.',
        'correo.email'              => 'Debe ingresar un correo válido.',
        'correo.unique'             => 'El correo ya está registrado.',
        'direccion.required'        => 'La dirección es obligatoria.',
    ]);

    $paciente->update($request->all());

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
}
