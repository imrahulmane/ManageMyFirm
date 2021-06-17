<?php


use Slim\Http\UploadedFile;


function saveImageToFolder($url, $name) {
    $path = "/var/www/html/ManageMyFirm/app/public/images/$name";
    $result = file_put_contents($path, file_get_contents($url));

    if($result == false) {
        return false;
    }

    return $path;
}

function moveUploadedFile(UploadedFile $uploadedFile, $name)
{
    $directory = __DIR__ . '/../public/images/';
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
//    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $name, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    $uploadedFilePath = $directory . DIRECTORY_SEPARATOR . $filename;

    return $uploadedFilePath;
}

