<?php

namespace App\Http\Controllers;

use App\Models\Consumption;
use App\Models\Activity;

use App\Http\Requests\StoreConsumptionRequest;
use App\Http\Requests\UpdateConsumptionRequest;

class ConsumptionController extends Controller
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
    public function store(StoreConsumptionRequest $request, Activity $activity)
    {
        $this->authorize('create', [Consumption::class, $activity]);

        $consumption = new Consumption(['name' => $request['name'], 'volume' => $request['volume']]);

        $activity->consumptions()->save($consumption);
    }

    /**
     * Display the specified resource.
     */
    public function show(Consumption $consumption)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsumptionRequest $request, Consumption $consumption)
    {
        $this->authorize('update', $consumption);

        $consumption->volume = $request['volume'];
        $consumption->name = $request['name'];
        $consumption->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consumption $consumption)
    {
        //
    }
}
