<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Family;

use App\Http\Resources\InvitationResource;
use App\Http\Resources\FamilyResource;

use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\AcceptInvitationRequest;
use Illuminate\Http\Resources;
use Illuminate\Contracts\Support\Responsable;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Responsable
    {
        $invitations = Invitation::where('email', auth()->user()->email)->orderBy('created_at')->get();
        return InvitationResource::collection($invitations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvitationRequest $request, Family $family): Responsable
    {
        $this->authorize('create', [Invitation::class, $family]);

        $validated = $request->validated();

        $invitation = $family->inviteAdult(
            auth()->user(),
            $validated['email'],
            $validated['name']
        );

        return new InvitationResource($invitation);
    }

    /**
     * Accept an invitation and place the user into the family that invited them
     */
    public function accept(AcceptInvitationRequest $request, Invitation $invitation): Responsable
    {
        $this->authorize('accept', $invitation);

        $relation = $request->validated()['relation'];

        $invitation->family->addAdult(auth()->user(), $relation);
        $family = $invitation->family;
        $invitation->forceDelete();

        return new FamilyResource($family);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invitation $invitation): Response
    {
        //
    }
}
