<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'correo' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('pacientes', 'correo'),
            ],
            'dpi' => ['required', 'string', 'max:20', Rule::unique('pacientes', 'dpi')],
            'fecha_nacimiento' => ['required', 'date'],
            'telefono' => ['required', 'string', 'max:20'],
            'direccion' => ['required', 'string', 'max:255'],
            'sexo' => ['nullable', 'string', 'max:50'],
            'estado_civil' => ['nullable', 'string', 'max:100'],
            'ocupacion' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['nombre_completo'],
                'email' => $validated['correo'],
                'role' => User::ROLE_PACIENTE,
                'password' => Hash::make($validated['password']),
            ]);

            Paciente::create([
                'user_id' => $user->id,
                'nombre_completo' => $validated['nombre_completo'],
                'dpi' => $validated['dpi'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'telefono' => $validated['telefono'],
                'correo' => $validated['correo'],
                'direccion' => $validated['direccion'],
                'sexo' => $validated['sexo'] ?? null,
                'estado_civil' => $validated['estado_civil'] ?? null,
                'ocupacion' => $validated['ocupacion'] ?? null,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route($user->homeRoute());
    }
}
