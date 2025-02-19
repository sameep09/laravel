<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffSubGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'staff_sub_groups';

    public function applicant_sub_group()
    {
        return $this->hasMany(Applicant::class, 'sub_group', 'id');
    }
}
