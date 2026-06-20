<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\User;

class PacientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessBackoffice();
    }

    public function view(User $user, Paciente $paciente): bool
    {
        if ($user->canAccessBackoffice()) {
            return true;
        }

        $user->loadMissing('paciente');

        return $user->isPaciente()
            && $user->paciente
            && $user->paciente->id === $paciente->id;
    }

    public function manage(User $user): bool
    {
        return $user->canAccessBackoffice();
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Paciente $paciente): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, Paciente $paciente): bool
    {
        return $this->manage($user);
    }
}
