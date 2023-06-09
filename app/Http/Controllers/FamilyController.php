<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\User;
use App\Http\Requests\StorefamilyRequest;
use App\Http\Requests\UpdatefamilyRequest;
use App\Http\Resources\FamilyResource;

use Illuminate\Http\Request;

class FamilyController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Family::class, 'family');
    }

    public function transferManager(Request $request, Family $family, User $user)
    {
        $auth = auth()->user();
        $this->authorize('transferManager', [$family, $user]);

        $family->transferManager($auth, $user);
    }

    public function removeAdult(Request $request, Family $family, User $user)
    {
        $this->authorize('removeAdult', [$family, $user]);
        $family->removeAdult($user);
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
    public function destroy(User $user, family $family)
    {
        $this->authorize('delete', $family);

        $family->delete();
    }
}
