<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'staff_groups';

    public function applicant_group()
    {
        return $this->hasMany(Applicant::class, 'group', 'id');
    }
}
