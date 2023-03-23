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
        return $this->adults()->where('manager', 1)->first(); //->where('manager', 1)->first();
    }

    public function inviteAdult(User $sender, $email, $name): Invitation
    {
        throw_if(
            (!$this->adults->contains($sender) ||
                !$this->adults->find($sender)->member->manager == 1
            ),
            AuthorizationException::class,
            'This user is not authorized send and invitation to this family.'
        );

        $expiration = now()
            ->addMonths(config('invitations.expliration.months'))
            ->addDays(config('invitations.expiration.days'));

        $invitation = $this->invitations()->firstOrNew(['email' => $email]);
        $invitation->name = $name;
        $invitation->expiration = $expiration;
        $invitation->save();

        InvitationCreated::dispatch($sender, $invitation);

        return $invitation;
    }

    public function addAdult(User $user, String $relation)
    {
        $this->adults()->attach($user, ['relation' => $relation]);
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
}
