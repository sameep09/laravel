<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeetingAttendee extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'meeting_attendees';

    //relation between meetingAttendee and Ministry
    public function ministry()
    {
        return $this->belongsTo(Ministry::class)->withDefault();
    }

    //relation between meeting and post
    public function post()
    {
        return $this->belongsTo(StaffPost::class, 'participant_post')->withDefault();
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class)->withDefault();
    }
}
