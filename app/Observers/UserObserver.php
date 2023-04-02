<?php

namespace App\Observers;

use App\Models\User;

use Laravolt\Avatar\Facade as Avatar;
use Illuminate\Support\Facades\Storage;

class UserObserver
{

    public $afterCommit = true;

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $filename = $user->id . config('avatars.file.type');
        $base = config('avatars.path.users.base');
        $location = config('avatars.path.users.db');

        Avatar::create($user->name)->save($base . $location . $filename);

        $user->avatar = $location . $filename;
        $user->save();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $base = config('avatars.path.users.base');
        if (Storage::exists($base . $user->avatar)) {
            Storage::delete($base . $user->avatar);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
    }
}
