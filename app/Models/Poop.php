<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poop extends Model
{
    use HasFactory;

    public function child()
    {
        return $this->hasOne(Child::class);
    }
}
