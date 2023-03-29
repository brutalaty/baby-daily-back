<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Family;

use Illuminate\Auth\Access\Response;


class InvitationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invitation $invitation): bool
    {
        return $invitation->family->getManager()->id == $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Family $family): bool
    {
        return $family->isManager($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invitation $invitation, String $status): bool
    {
        if ($invitation->expiration < now()) return false;
        if ($invitation->status != config('invitations.status.unaccepted')) return false;

        if ($status == config('invitations.status.accepted')) {
            return $user->email == $invitation->email;
        }

        if ($status == config('invitations.status.declined')) {
            return $user->email == $invitation->email;
        }

        if ($status == config('invitations.status.canceled')) {
            return $invitation->family->isManager($user);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invitation $invitation): bool
    {
        //return $family->boss->id == $user->id;
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invitation $invitation): bool
    {
        //return $family->boss->id == $user->id;
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invitation $invitation): bool
    {
        //return $family->boss->id == $user->id;
        return false;
    }
}
