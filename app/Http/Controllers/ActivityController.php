<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\Child;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActivityRequest $request, Child $child)
    {
        $this->authorize('create', [Activity::class, $child]);

        $activity = $child->addNewActivity(
            $request['type'],
            $request['time']
        );

        return new ActivityResource($activity);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        $this->authorize('update', $activity);

        $activity->time = $request['time'];
        $activity->save();

        return new ActivityResource($activity);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        $this->authorize('delete', $activity);
        $activity->delete();
    }
}
