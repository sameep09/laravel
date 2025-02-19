<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'countries';

    //relation between meeting_agenda and country
    public function meeting_agenda()
    {
        return $this->hasMany(MeetingAgenda::class);
    }

    public function study_leave()
    {
        return $this->hasMany(StudyLeave::class);
    }

    public function forAjax()
    {
        return [
            'id' => $this->id,
            'show_name' => $this->country
        ];
    }
}
