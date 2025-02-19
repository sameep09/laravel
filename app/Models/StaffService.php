<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffService extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'staff_services';

    public function applicant_service()
    {
        return $this->hasMany(Applicant::class, 'service', 'id');
    }
}
