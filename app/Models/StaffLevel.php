<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'staff_levels';

    public function applicant_level()
    {
        return $this->hasMany(Applicant::class, 'level', 'id');
    }
}
