<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Child;
use App\Models\Family;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ChildPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Child $child)
    {
        return $child->family->adults->contains($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Family $family
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Family $family)
    {
        return $family->adults->contains($user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Child $child)
    {
        return $child->family->adults->contains($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Child $child)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Child $child)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Child $child)
    {
        //
    }
}
