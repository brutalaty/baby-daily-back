<?php

namespace App\Policies;

use App\Models\User;
use App\Models\family;
use Illuminate\Auth\Access\HandlesAuthorization;

class FamilyPolicy
{
    use HandlesAuthorization;


    public function transferManager(User $auth, Family $family, User $user)
    {
        return $family->isManager($auth) && $family->adults->contains($user);
    }


    /**
     * Determine whether this authenticated user can remove the user from the family
     * Managers can only remove other users
     * Non Managers can only remove themselves
     */
    public function removeAdult(User $auth, Family $family, User $user)
    {
        if (!$family->adults->contains($user)) return false;

        if ($family->isManager($auth)) return $auth->id != $user->id;

        return $auth->id == $user->id;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\family  $family
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, family $family)
    {
        return $family->adults->contains($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\family  $family
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, family $family)
    {
        return $family->adults->contains($user);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\family  $family
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, family $family)
    {
        return $family->adults->contains($user);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\family  $family
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, family $family)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\family  $family
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, family $family)
    {
        //
    }
}
