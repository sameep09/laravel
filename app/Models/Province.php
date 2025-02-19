<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'provinces';

    //relation between province and districts
    public function districts()
    {
        return $this->hasMany(District::class);
    }

    //relation between province and local_levels
    public function local_levels()
    {
        return $this->hasMany(LocalLevel::class);
    }

    //relation between province and office
    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}
