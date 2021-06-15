<?php


function saveImageToFolder($url, $name) {
    $path = "/var/www/html/ManageMyFirm/app/public/images/$name";
    $result = file_put_contents($path, file_get_contents($url));

    if($result == false) {
        return false;
    }

    return true;
}

//$url = "https://picsum.photos/id/0/200/300";
//$path = "/var/www/html/ManageMyFirm/app/public/images/twitter.png";
//file_put_contents($path, file_get_contents($url));

