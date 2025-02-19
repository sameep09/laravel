<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'applicants';

    //relation between agenda_ministry and ministry
    public function ministry()
    {
        return $this->belongsTo(Ministry::class, 'ministry_id')->withDefault();
    }

    //relation between agenda_ministry and ministry
    public function agendaMinistry()
    {
        return $this->belongsTo(AgendaMinistry::class, 'agenda_ministry_id')->withDefault();
    }

    //relation between agenda_ministry and MeetingAgenda
    public function meetingAgenda()
    {
        return $this->belongsTo(MeetingAgenda::class, 'meeting_agenda_id')->withDefault();
    }

    //relation between agenda_ministry and NominationMeeting
    public function nominationMeeting()
    {
        return $this->belongsTo(NominationMeeting::class, 'nomination_meeting_id')->withDefault();
    }

    //relation between applicant and post
    public function staffPost()
    {
        return $this->belongsTo(StaffPost::class, 'post')->withDefault();
    }

    //relation between applicant and service
    public function staffService()
    {
        return $this->belongsTo(StaffService::class, 'service')->withDefault();
    }

    //relation between applicant and group
    public function staffGroup()
    {
        return $this->belongsTo(StaffGroup::class, 'group')->withDefault();
    }

    //relation between applicant and sub_group
    public function staffSubGroup()
    {
        return $this->belongsTo(StaffSubGroup::class, 'sub_group')->withDefault();
    }

    //relation between applicant and sub_group
    public function staffLevel()
    {
        return $this->belongsTo(StaffLevel::class, 'level')->withDefault();
    }
}
