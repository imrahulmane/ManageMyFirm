<?php


namespace App\Exceptions;


class ExceptionHandler
{
    protected function isDebug() {
        return getenv('DEBUG') == "true" ;
    }
}
