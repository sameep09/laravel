<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaMinistry extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'agenda_ministries';

    public function applicant_agenda_ministry()
    {
        return $this->hasMany(Applicant::class, 'agenda_ministry_id', 'id');
    }

    //relation between agenda_ministry and meeting
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    //relation between agenda_ministry and ministry
    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    //relation between agenda_ministry and MeetingAgenda
    public function meetingAgenda()
    {
        return $this->belongsTo(MeetingAgenda::class, 'agenda_id');
    }

    //relation between agenda_ministry and NominationMeeting
    public function nominationMeeting()
    {
        return $this->belongsTo(NominationMeeting::class, 'nomination_meeting_id');
    }
}
