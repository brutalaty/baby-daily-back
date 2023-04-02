<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'born'];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function poops()
    {
        return $this->hasMany(Poop::class);
    }

    public function avatarUrl(): String
    {
        return asset('storage/' . $this->avatar);
    }
}
