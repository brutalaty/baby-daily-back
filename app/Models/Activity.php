<?php

namespace App\Models;

use App\Models\Child;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{

    protected $fillable = ['time', 'type'];

    use HasFactory;

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
