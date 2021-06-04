<?php

use App\Exceptions\ErrorHandler;
use App\Exceptions\PhpErrorHandler;
use Slim\App;

$app = new App(['settings' => ['displayErrorDetails' => true]]);

$c = $app->getContainer();

$c['errorHandler'] = function ($c) {
    return new ErrorHandler();
};

$c['phpErrorHandler'] = function ($c) {
    return new PhpErrorHandler();
};
