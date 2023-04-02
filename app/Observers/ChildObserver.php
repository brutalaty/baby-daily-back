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

        Avatar::create($child->name)->setTheme('pastel')->save($base . $location . $filename);

        $child->avatar = $filename;
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
        if (Storage::disk('children')->exists($child->avatar)) {
            Storage::disk('children')->delete($child->avatar);
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
