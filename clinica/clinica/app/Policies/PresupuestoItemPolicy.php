<?php

namespace App\Policies;

use App\Models\Consulta;
use App\Models\ConsultaPresupuestoItem;
use App\Models\User;

class PresupuestoItemPolicy
{
    public function create(User $user, Consulta $consulta): bool
    {
        return $user->canManageClinicalHistory();
    }

    public function viewSuggestions(User $user, Consulta $consulta): bool
    {
        return $user->canManageClinicalHistory();
    }

    public function accept(User $user, Consulta $consulta): bool
    {
        return $user->canManageClinicalHistory();
    }

    public function update(User $user, ConsultaPresupuestoItem $item): bool
    {
        return $user->canManageClinicalHistory();
    }

    public function delete(User $user, ConsultaPresupuestoItem $item): bool
    {
        return $user->canManageClinicalHistory();
    }
}
