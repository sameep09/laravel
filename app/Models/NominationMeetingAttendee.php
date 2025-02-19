<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NominationMeetingAttendee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'nomination_meeting_attendees';

    //relation between nMeetingAttendee and Ministry
    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    //relation between nmeeting and post
    public function post()
    {
        return $this->belongsTo(StaffPost::class, 'participant_post');
    }
}
