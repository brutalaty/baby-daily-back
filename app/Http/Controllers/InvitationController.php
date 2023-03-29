<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Family;

use App\Http\Resources\InvitationResource;
use App\Http\Resources\FamilyResource;

use App\Http\Requests\StoreInvitationRequest;
use App\Http\Requests\UpdateInvitationRequest;
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

        $this->cancelExistingInvitation($family, $validated['email']);

        $invitation = $family->inviteAdult(
            auth()->user(),
            $validated['email'],
            $validated['name'],
            $validated['relation']
        );

        return new InvitationResource($invitation);
    }

    /**
     * If there is an invitation that has not expired, been canceled or been declined, cancel it
     */
    private function cancelExistingInvitation(Family $family, String $email)
    {
        $invitation = Invitation::where('family_id', $family->id)->where('email', $email)->where('expiration', '>', now())->first();

        if (!$invitation) return;

        $invitation->status = config('invitations.status.canceled');
        $invitation->save();
    }

    /**
     * Accept an invitation and place the user into the family that invited them
     */
    public function update(UpdateInvitationRequest $request, Invitation $invitation): Responsable
    {
        $status = $request->validated()['status'];
        $this->authorize('update', [$invitation, $status]);

        $invitation->family->addAdult(auth()->user(), $invitation->relation);

        $invitation->status = $status;
        $invitation->save();

        $family = $invitation->family;

        return new FamilyResource($family);
    }
}
