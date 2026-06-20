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
        $this->authorize('viewAny', Paciente::class);

        $buscar = request('buscar');

        $pacientes = Paciente::with('user')
            ->when($buscar, function (Builder $query, string $buscar) {
                return $query->where(function (Builder $query) use ($buscar) {
                    $query->where('nombre_completo', 'like', "%$buscar%")
                        ->orWhere('dpi', 'like', "%$buscar%")
                        ->orWhere('telefono', 'like', "%$buscar%");
                });
            })
            ->orderBy('nombre_completo')
            ->paginate(25)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Paciente::class);

        return view('pacientes.create', [
            'usuariosPaciente' => $this->usuariosPacienteDisponibles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePacienteRequest $request)
    {
        $this->authorize('create', Paciente::class);

        Paciente::create($request->validated());

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Paciente $paciente)
    {
        $this->authorize('view', $paciente);

        $paciente->load([
            'user',
            'antecedenteClinico',
            'consultas' => fn ($q) => $q->orderByDesc('fecha')->limit(5),
            'consultas.presupuestoItems',
            'pagos' => fn ($q) => $q->orderByDesc('fecha_pago')->orderByDesc('created_at'),
        ]);

        $consultasParaAbono = $paciente->consultas()
            ->orderByDesc('fecha')
            ->get(['id', 'fecha', 'motivo'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'label' => $c->fecha->format('d/m/Y').' — '.($c->motivo ?: 'Sin motivo'),
            ]);

        return view('pacientes.show', [
            'paciente' => $paciente,
            'consultasParaAbono' => $consultasParaAbono,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paciente $paciente)
    {
        $this->authorize('update', $paciente);

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
        $this->authorize('update', $paciente);

        $paciente->update($request->validated());

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paciente $paciente)
    {
        $this->authorize('delete', $paciente);

        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente eliminado correctamente.');
    }

    public function evolucionOdontograma(Paciente $paciente)
    {
        $this->authorize('view', $paciente);

        $consultas = $paciente->consultas()
            ->with(['piezasDentales' => fn ($q) => $q->orderBy('cuadrante')->orderBy('posicion')])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->get();

        $evoluciones = [];

        foreach ($consultas as $consulta) {
            foreach ($consulta->piezasDentales as $pieza) {
                $estado = $pieza->pivot?->estado ?? 'sana';

                if (! isset($evoluciones[$pieza->id])) {
                    $evoluciones[$pieza->id] = [
                        'numero' => $pieza->numero,
                        'nombre' => $pieza->nombre,
                        'cuadrante' => $pieza->cuadrante,
                        'estado_actual' => $estado,
                        'cambios' => [],
                    ];
                }

                $evoluciones[$pieza->id]['cambios'][] = [
                    'consulta_id' => $consulta->id,
                    'fecha' => $consulta->fecha,
                    'fecha_iso' => $consulta->fecha?->toDateString(),
                    'motivo' => $consulta->motivo,
                    'estado' => $estado,
                    'observaciones' => $pieza->pivot?->observaciones,
                ];
            }
        }

        // Ordenar por número de pieza ascendente para presentación estable.
        uasort($evoluciones, fn ($a, $b) => $a['numero'] <=> $b['numero']);

        return view('pacientes.odontograma-evolucion', [
            'paciente' => $paciente,
            'evoluciones' => array_values($evoluciones),
            'totalConsultas' => $consultas->count(),
        ]);
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
