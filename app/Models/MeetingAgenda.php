<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeetingAgenda extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'meeting_agendas';

    //relation between meeting_agenda and country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class)->withDefault();
    }

    //relation between meeting_agenda and program_type
    public function program_type()
    {
        return $this->belongsTo(ProgramType::class, 'type');
    }

    //relation between meeting_agenda and agenda_ministry
    public function agenda_ministry()
    {
        return $this->hasMany(AgendaMinistry::class, 'agenda_id', 'id');
    }

    //relation between meeting_agenda and agenda_ministry
    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    public function applicants_main()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_nominated', '2');
    }

    public function applicants_sub()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_nominated', '3');
    }

    public function applicants_no()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_nominated', '0');
    }

    public function applicants_yes()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_nominated', '1');;
    }

    public function applicants_final_main()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_selected', '2');
    }

    public function applicants_final_sub()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_selected', '3');
    }

    public function applicants_final_no()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_selected', '0');
    }

    public function applicants_final_yes()
    {
        return $this->hasMany(Applicant::class, 'meeting_agenda_id', 'id')->where('is_selected', '1');;
    }
}
