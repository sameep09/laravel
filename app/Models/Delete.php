<?php

namespace App\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;

class Delete extends Model
{
    protected static function check($data, $relations)
    {
        $ok_delete = true;
        foreach ($relations as $r) {
            if ($data->$r()->count() > 0) {
                $ok_delete = false;
                break;
            }
        }

        return $ok_delete;
    }

    public static function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        if (file_exists($dirPath))
            rmdir($dirPath);
    }
}
