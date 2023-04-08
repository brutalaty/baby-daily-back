<?php

namespace App\Policies;

use App\Models\Consumption;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsumptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Consumption $consumption): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Activity $activity): bool
    {
        return $activity->child->family->adults->contains($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Consumption $consumption): bool
    {
        return $consumption->activity->child->family->adults->contains($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Consumption $consumption): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Consumption $consumption): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Consumption $consumption): bool
    {
        //
    }
}
