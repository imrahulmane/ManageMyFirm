<?php
use App\controllers\ServiceController;
require __DIR__ . '/../util/slim.php';


$app->post('/service', function ($request, $response){
    $data = $request->getParam('data');
    $data = json_decode($data, true);
    $serviceController = new ServiceController();
    $result = $serviceController->addService($data);
    return $response->withJson($result);
});

$app->put('/service/{service_id}', function ($request, $response){
    $serviceId = $request->getAttribute('service_id');
    $data = $request->getParam('data');
    $data = json_decode($data, true);
    $serviceController = new ServiceController();
    $result = $serviceController->updateService($serviceId, $data);
    return $response->withJson($result);
});

$app->get('/service/{customer_id}', function ($request, $response){
    $customerId = $request->getAttribute('customer_id');
    $serviceController = new ServiceController();
    $result = $serviceController->getService($customerId);
    return $response->withJson($result);
});

$app->get('/service', function ($request, $response){
    $searchCriteria = $request->getParam('data');
    $searchCriteria = json_decode($searchCriteria, 1);
    $serviceController = new ServiceController();
    $result = $serviceController->getAllServices($searchCriteria);
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
    $result = $serviceController->completeService($serviceId);
    return $response->withJson($result);
});

$app->get('/services', function ($request, $response){
    $serviceController = new ServiceController();
    $result = $serviceController->stats();
    return $response->withJson($result);
});


//$app->post('service', function ($request, $response){});


$app->run();