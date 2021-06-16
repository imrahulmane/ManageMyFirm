<?php


function saveImageToFolder($url, $name) {
    $path = "/var/www/html/ManageMyFirm/app/public/images/$name";
    $result = file_put_contents($path, file_get_contents($url));

    if($result == false) {
        return false;
    }

    return $path;
}

