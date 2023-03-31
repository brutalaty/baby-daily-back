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

        $family->cancelInvitationTo($validated['email']);

        $invitation = $family->inviteAdult(
            auth()->user(),
            $validated['email'],
            $validated['name'],
            $validated['relation']
        );

        return new InvitationResource($invitation);
    }

    /**
     * Consume the invitation, changing status to  accepted, declined or canceled
     */
    public function update(UpdateInvitationRequest $request, Invitation $invitation): mixed
    {
        $status = $request->validated()['status'];
        $this->authorize('update', [$invitation, $status]);

        return $this->consumeInvitation($invitation, $status);
    }


    private function consumeInvitation(Invitation $invitation, String $status)
    {
        if ($status == config('invitations.status.accepted')) {
            $invitation->accept(auth()->user());
            return new FamilyResource($invitation->family->fresh());
        } else if ($status == config('invitations.status.declined')) {
            $invitation->decline();
        } else {
            $invitation->cancel();
        }

        return Response()->noContent();
    }
}
