<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Family;
use App\Models\User;

use App\Http\Requests\StoreChildRequest;
use App\Http\Requests\UpdateChildRequest;
use App\Http\Requests\UpdateAvatarRequest;

use App\Http\Resources\ChildResource;

class ChildController extends Controller
{

    public function avatar(UpdateAvatarRequest $request, User $user, Child $child)
    {
        $this->authorize('avatar', $child);
        $file = $request->file('avatar');

        $path = $file->storeAs(
            '/',
            $child->id . '.' . $file->extension(),
            'children'
        );

        $child->updateAvatar($path);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorechildRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChildRequest $request, Family $family)
    {
        $this->authorize('create', [Child::class, $family]);

        $validated = $request->validated();

        $child = $family->addNewChild(
            $validated['name'],
            $validated['born']
        );

        return new ChildResource($child);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function show(Child $child)
    {
        $this->authorize('view', $child);

        return new ChildResource($child);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatechildRequest  $request
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChildRequest $request, User $user, Child $child)
    {
        $this->authorize('update', $child);

        $child->fill($request->validated())->save();

        return new ChildResource($child->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Child  $child
     * @return \Illuminate\Http\Response
     */
    public function destroy(child $child)
    {
        //
    }
}
