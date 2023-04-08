<?php

namespace App\Models;

use App\Models\Child;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = ['time', 'type'];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class);
    }
}
