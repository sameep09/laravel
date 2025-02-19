<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ministry extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'ministries';

    //relation with meeting_attendees
    public function meeting_attendees()
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function agenda_ministry()
    {
        return $this->hasMany(MeetingAttendee::class);
    }
}
