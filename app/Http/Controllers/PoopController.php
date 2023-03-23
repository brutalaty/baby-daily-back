<?php

namespace App\Http\Controllers;

use App\Models\poop;
use App\Http\Requests\StorepoopRequest;
use App\Http\Requests\UpdatepoopRequest;

class PoopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorepoopRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorepoopRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\poop  $poop
     * @return \Illuminate\Http\Response
     */
    public function show(poop $poop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatepoopRequest  $request
     * @param  \App\Models\poop  $poop
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatepoopRequest $request, poop $poop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\poop  $poop
     * @return \Illuminate\Http\Response
     */
    public function destroy(poop $poop)
    {
        //
    }
}
