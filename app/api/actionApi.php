<?php

use App\controllers\ActionController;
use Slim\App;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$app = new App($configuration);

$app->post('/action', function ($request, $response){
    $data = $request->getParsedBody();
    $actionController = new ActionController();
});
$app->put('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $data = $request->getParsedBody();
    $actionController = new ActionController();
});
$app->get('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $actionController = new ActionController();
});
$app->get('/action', function ($request, $response){
    $actionController = new ActionController();
});
$app->delete('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $actionController = new ActionController();
});
$app->patch('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $actionController = new ActionController();
});
$app->get('/actions/search', function ($request, $response){
    $data = $request->getParam('data');
    $actionController = new ActionController();
});

//$app->post('/', function ($request, $response){});


$app->run();
