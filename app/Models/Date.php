<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $table = 'dates';

    public $timestamps = false;

    static function nepToEng($nepDate)
    {
        $dateData = Date::select('engdate')
            ->where('nepdate', sanitize($nepDate))
            ->first();

        return $dateData->engdate;
    }

    static function engToNep($engDate)
    {
        $engDate = sanitize($engDate);

        $dateData = Date::select('nepdate')
            ->where('engdate', $engDate)
            ->first();

        return $dateData->nepdate;
    }
}
