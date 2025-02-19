<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'meetings';

    //relation between meeting and post
    public function post()
    {
        return $this->belongsTo(StaffPost::class, 'chaired_by_post');
    }

    public function meeting_attendee()
    {
        return $this->hasMany(MeetingAttendee::class, 'meeting_id', 'id');
    }

    public function meeting_agenda()
    {
        return $this->hasMany(MeetingAgenda::class, 'meeting_id', 'id');
    }

    public function agenda_ministry()
    {
        return $this->hasMany(AgendaMinistry::class, 'meeting_id', 'id');
    }

    public function forAjax()
    {
        return [
            'id' => $this->id,
            'show_name' => $this->meeting_number . " " . $this->meeting_date . " " . $this->meeting_time . " " . $this->chaired_by
        ];
    }
}
