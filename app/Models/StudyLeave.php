<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudyLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'study_leaves';

    //relation between study_leave and country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    //relation between study_leave and ministry
    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }
}
