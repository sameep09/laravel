<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'staff_posts';

    public function meeting_post()
    {
        return $this->hasMany(Meeting::class, 'chaired_by_post', 'id');
    }

    public function nom_meeting_post()
    {
        return $this->hasMany(NominationMeeting::class, 'chaired_by_post', 'id');
    }

    public function applicant_post()
    {
        return $this->hasMany(Applicant::class, 'post', 'id');
    }
}
