<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'program_types';

    //relation between meeting_agenda and programType
    public function meeting_agenda()
    {
        return $this->hasMany(MeetingAgenda::class, 'type', 'id');
    }

    public function forAjax()
    {
        return [
            'id' => $this->id,
            'show_name' => $this->type
        ];
    }
}
