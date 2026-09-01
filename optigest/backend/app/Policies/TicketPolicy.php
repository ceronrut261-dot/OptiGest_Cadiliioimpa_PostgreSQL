<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * Administrador y cotizador pueden ver, crear y listar tickets sin
     * restricción. Cualquier usuario autenticado puede ver el listado y
     * crear tickets (comportamiento previo, sin cambios).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Solo puede actualizar un ticket: administrador, cotizador, o el
     * técnico al que el ticket está actualmente asignado. Un técnico no
     * puede editar tickets de otros técnicos ni tickets sin asignar.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole(['administrador', 'cotizador'])) {
            return true;
        }

        return $user->hasRole('tecnico') && $ticket->tecnico_id === $user->id;
    }

    /**
     * Cambiar el estado sigue la misma regla que actualizar: dueño del
     * ticket o administrador/cotizador.
     */
    public function cambiarEstado(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    /**
     * Cancelar (destroy) un ticket es una acción más sensible: se limita
     * a administrador y cotizador. Un técnico no puede cancelar tickets,
     * ni siquiera los suyos, para evitar que oculte trabajo pendiente.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(['administrador', 'cotizador']);
    }
}
