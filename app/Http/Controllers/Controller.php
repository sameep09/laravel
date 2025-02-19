<?php

namespace App\Http\Controllers;

use auth;
use App\Models\UserLog;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function actionlog($table, $table_id, $data)
    {
        UserLog::create([
            'user_id' => auth()->user()->id,
            'which_table' => $table,
            'which_table_id' => $table_id,
            'action_type' => "Edit",
            'edited_data' => $data,
        ]);

        return true;
    }
}
