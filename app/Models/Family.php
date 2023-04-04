<?php

namespace App\Models;

use App\Models\Invitation;
use App\Events\InvitationCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Auth\Access\AuthorizationException;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'relation',
    ];

    protected $with = ['adults', 'children', 'invitations'];

    public function children()
    {
        return $this->hasMany(Child::class);
    }

    public function adults()
    {
        return $this->belongsToMany(User::class)->as('member')->withPivot('relation', 'manager');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function getManager()
    {
        return $this->adults()->where('manager', 1)->first();
    }

    public function inviteAdult(User $sender, $email, $name, $relation): Invitation
    {
        $expiration = now()
            ->addMonths(config('invitations.expiration.months'))
            ->addDays(config('invitations.expiration.days'));

        $invitation = new Invitation();
        $invitation->email = $email;
        $invitation->name = $name;
        $invitation->relation = $relation;
        $invitation->expiration = $expiration;
        $this->invitations()->save($invitation);

        $invitation->refresh(); //reloading for DB defaults

        InvitationCreated::dispatch($sender, $invitation);

        return $invitation;
    }

    public function isManager(User $user): bool
    {
        return $this->adults->contains($user) && $this->adults->find($user)->member->manager == 1;
    }

    public function addAdult(User $user, String $relation)
    {
        $this->adults()->attach($user, ['relation' => $relation]);
    }

    public function removeAdult(User $user)
    {
        $this->adults()->detach($user);
    }

    public function addNewChild(String $name, String $born)
    {
        $child = new Child();
        $child->name = $name;
        $child->born = $born;
        $child->family_id = $this->id;
        $child->save();

        return $child;
    }

    /**
     * Cancel an unaccepted and unexpired invitations to provided email if it exists
     */
    public function cancelInvitationTo(String $email)
    {
        $invitation = $this->invitations()->where('email', $email)->where('expiration', '>', now())->first();

        if ($invitation) $invitation->cancel($invitation);
    }
}
