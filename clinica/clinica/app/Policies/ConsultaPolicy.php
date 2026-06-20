<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\Paciente;
use App\Models\User;

class ConsultaPolicy
{
    public function viewAny(User $user, ?Paciente $paciente = null): bool
    {
        if ($user->canAccessBackoffice()) {
            return true;
        }

        if (! $paciente) {
            return false;
        }

        $user->loadMissing('paciente');

        return $user->isPaciente()
            && $user->paciente
            && $user->paciente->id === $paciente->id;
    }

    public function view(User $user, Consulta $consulta): bool
    {
        if ($user->canAccessBackoffice()) {
            return true;
        }

        $user->loadMissing('paciente');

        return $user->isPaciente()
            && $user->paciente
            && $consulta->paciente_id === $user->paciente->id;
    }

    public function create(User $user, Paciente $paciente): bool
    {
        return $user->canManageClinicalHistory();
    }

    public function update(User $user, Consulta $consulta): bool
    {
        return $user->canManageClinicalHistory();
    }

    public function manage(User $user, Consulta $consulta): bool
    {
        return $user->canManageClinicalHistory();
    }
}
