<?php

namespace App\Observers;

use App\Models\Child;

use Illuminate\Support\Facades\Storage;
use Laravolt\Avatar\Facade as Avatar;

class ChildObserver
{

    public $afterCommit = true;

    /**
     * Handle the Child "created" event.
     */
    public function created(Child $child): void
    {
        $filename = $child->id . config('avatars.file.type');
        $base = config('avatars.path.children.base');
        $location = config('avatars.path.children.db');

        Avatar::create($child->name)->save($base . $location . $filename);

        $child->avatar = $location . $filename;
        $child->save();
    }

    /**
     * Handle the Child "updated" event.
     */
    public function updated(Child $child): void
    {
        //
    }

    /**
     * Handle the Child "deleted" event.
     */
    public function deleted(Child $child): void
    {
        $base = config('avatars.path.children.base');
        if (Storage::exists($base . $child->avatar)) {
            Storage::delete($base . $child->avatar);
        }
    }

    /**
     * Handle the Child "restored" event.
     */
    public function restored(Child $child): void
    {
        //
    }

    /**
     * Handle the Child "force deleted" event.
     */
    public function forceDeleted(Child $child): void
    {
        //
    }
}
