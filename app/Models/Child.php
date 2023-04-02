<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

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

    public function updateAvatar(String $avatar)
    {
        if ($this->avatar != $avatar) {
            Storage::disk('children')->delete($this->avatar);
        }

        $this->avatar = $avatar;
        $this->save();
    }

    public function avatarUrl(): String
    {
        // return asset('storage/' . $this->avatar);
        return Storage::disk('children')->url($this->avatar);
    }
}
