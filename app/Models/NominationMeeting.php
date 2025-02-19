<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NominationMeeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'nomination_meetings';

    //relation with agenda_ministry
    public function agenda_ministry()
    {
        return $this->hasMany(AgendaMinistry::class, 'nomination_meeting_id', 'id');
    }

    public function applicant_agenda_ministry()
    {
        return $this->hasMany(Applicant::class, 'nomination_meeting_id', 'id');
    }

    //relation between nmeeting and post
    public function post()
    {
        return $this->belongsTo(StaffPost::class, 'chaired_by_post');
    }

    public function forAjax()
    {
        return [
            'id' => $this->id,
            'show_name' => $this->meeting_number . " " . $this->meeting_date . " " . $this->meeting_time . " " . $this->chaired_by
        ];
    }
}
