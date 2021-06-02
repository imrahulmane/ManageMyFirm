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
    $result = $actionController->addAction($data);
    return $response->withJson($result);
});
$app->put('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $data = $request->getParsedBody();
    $actionController = new ActionController();
    $result = $actionController->updateAction($actionId, $data);
    return $response->withJson($result);
});
$app->get('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $actionController = new ActionController();
    $result = $actionController->getAction($actionId);
    return $response->withJson($result);
});
$app->get('/action', function ($request, $response){
    $actionController = new ActionController();
    $result = $actionController->getAllActions();
    return $response->withJson($result);
});
$app->delete('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $actionController = new ActionController();
    $result = $actionController->deleteAction($actionId);
    return $response->withJson($result);
});
$app->patch('/action/{action_id}', function ($request, $response){
    $actionId = $request->getAttribute('action_id');
    $actionController = new ActionController();
    $result = $actionController->closeAction($actionId);
    return $response->withJson($result);
});
$app->get('/actions/search', function ($request, $response){
    $data = $request->getParam('data');
    $data = json_decode($data, 1);
    $actionController = new ActionController();
    $result = $actionController->searchAction($data);
    return $response->withJson($result);
});

//$app->post('/', function ($request, $response){});


$app->run();
