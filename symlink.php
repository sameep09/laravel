<?php
$tFolder = __DIR__.'/storage/app/public';
$linkFolder = __DIR__.'/public/storage';

if (symlink($tFolder, $linkFolder)) {
    echo "Link Created Successfully.";
} else {
    echo "Unable to Create Link.";
}

// die('you lost your way!!');
