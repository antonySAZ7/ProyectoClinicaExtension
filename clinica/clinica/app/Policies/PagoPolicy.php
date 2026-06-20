<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\Pago;
use App\Models\User;

class PagoPolicy
{
    public function create(User $user, Paciente $paciente): bool
    {
        return $user->canAccessBackoffice();
    }

    public function view(User $user, Pago $pago): bool
    {
        if ($user->canAccessBackoffice()) {
            return true;
        }

        $user->loadMissing('paciente');

        return $user->isPaciente()
            && $user->paciente
            && $pago->paciente_id === $user->paciente->id;
    }
}
