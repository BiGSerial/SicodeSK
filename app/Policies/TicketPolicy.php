<?php

namespace App\Policies;

use App\Models\SicodeUser;
use App\Models\Ticket;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(SicodeUser $sicodeUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(SicodeUser $sicodeUser, Ticket $ticket): bool
    {
        return $ticket->requester_sicode_id === $sicodeUser->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(SicodeUser $sicodeUser): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(SicodeUser $sicodeUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(SicodeUser $sicodeUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(SicodeUser $sicodeUser, Ticket $ticket): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(SicodeUser $sicodeUser, Ticket $ticket): bool
    {
        return false;
    }
}
