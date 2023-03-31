<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use \DateTime;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email'];

    protected $hidden = ['email'];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function hasExpired(): bool
    {
        return (new DateTime($this->expiration) < now());
    }

    public function cancel()
    {
        $this->status = config('invitations.status.canceled');
        $this->save();
    }

    public function isCanceled()
    {
        return $this->status == config('invitations.status.canceled');
    }

    public function decline()
    {
        $this->status = config('invitations.status.declined');
        $this->save();
    }

    public function isDeclined()
    {
        return $this->status == config('invitations.status.declined');
    }

    public function accept(User $user)
    {
        $this->family->addAdult($user, $this->relation);

        $this->status = config('invitations.status.accepted');
        $this->save();
    }

    public function isAccepted()
    {
        return $this->status == config('invitations.status.accepted');
    }
}
