<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class District extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    protected $table = 'districts';

    //relation between province and district
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    //relation between district and local_levels
    public function local_levels()
    {
        return $this->hasMany(LocalLevel::class);
    }

    //relation between district and office
    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
