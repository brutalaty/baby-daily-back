<?php

namespace App\Http\Controllers;

use App\Models\family;
use App\Http\Requests\StorefamilyRequest;
use App\Http\Requests\UpdatefamilyRequest;
use App\Http\Resources\FamilyResource;

class FamilyController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Family::class, 'family');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return FamilyResource::collection(auth()->user()->families);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorefamilyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorefamilyRequest $request)
    {
        $user = auth()->user();
        $validated = $request->safe()->only(['name', 'relation']);
        $family = $user->createFamily($validated['name'], $validated['relation']);

        return new FamilyResource($family);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\family  $family
     * @return \Illuminate\Http\Response
     */
    public function show(Family $family)
    {
        return new FamilyResource($family);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatefamilyRequest  $request
     * @param  \App\Models\family  $family
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatefamilyRequest $request, family $family)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\family  $family
     * @return \Illuminate\Http\Response
     */
    public function destroy(family $family)
    {
        //
    }
}
