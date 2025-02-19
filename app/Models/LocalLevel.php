<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocalLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'local_level';

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    //relation between locallevel and office
    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
