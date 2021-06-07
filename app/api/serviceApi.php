<?php
use App\controllers\ServiceController;
require __DIR__ . '/../util/slim.php';


$app->post('/service', function ($request, $response){
    $data = $request->getParsedBody();
    $serviceController = new ServiceController();
    $result = $serviceController->addService($data);
    return $response->withJson($result);
});

$app->put('/service/{service_id}', function ($request, $response){
    $serviceId = $request->getAttribute('service_id');
    $data = $request->getParsedBody();
    $serviceController = new ServiceController();
    $result = $serviceController->updateService($serviceId, $data);
    return $response->withJson($result);
});

$app->get('/service/{service_id}', function ($request, $response){
    $serviceId = $request->getAttribute('service_id');
    $serviceController = new ServiceController();
    $result = $serviceController->getService($serviceId);
    return $response->withJson($result);
});

$app->get('/service', function ($request, $response){
    $serviceController = new ServiceController();
    $result = $serviceController->getAllServices();
    return $response->withJson($result);
});

$app->delete('/service/{service_id}', function ($request, $response){
    $serviceId = $request->getAttribute('service_id');
    $serviceController = new ServiceController();
    $result = $serviceController->deleteService($serviceId);
    return $response->withJson($result);
});

$app->patch('/service/{service_id}', function ($request, $response){
    $serviceId = $request->getAttribute('service_id');
    $serviceController = new ServiceController();
    $result = $serviceController->changeStatus($serviceId);
    return $response->withJson($result);
});

//$app->post('service', function ($request, $response){});


$app->run();