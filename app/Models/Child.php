<?php

namespace App\Models;

use App\Models\Activity;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Carbon;

class Child extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'born'];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function addNewActivity(String $type, String $time)
    {
        return $this->activities()->save(new Activity([
            'type' => $type,
            'time' => $time
        ]));
    }

    public function updateAvatar(String $filename)
    {
        if ($this->avatar != $filename) {
            Storage::disk('children')->delete($this->avatar);
            $this->avatar = $filename;
            $this->save();
        }
    }

    public function avatarUrl(): String
    {
        return Storage::disk('children')->url($this->avatar);
    }

    //Child Resource sees born as a string, anywhere else it's a carbon instance
    public function age(): String
    {
        $carbon = $this->convert_born_to_carbon($this->born);
        return $this->age_from_carbon($carbon);
    }

    private function age_from_carbon(Carbon $born): String
    {
        return $born->diffForHumans(['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 2]);
    }

    private function convert_born_to_carbon(String|Carbon $born): Carbon
    {
        if ($born instanceof Carbon) return $born;
        return Carbon::createFromDate($born);
    }
}
