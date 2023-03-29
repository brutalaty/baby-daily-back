<?php

namespace App\Models;

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
}
