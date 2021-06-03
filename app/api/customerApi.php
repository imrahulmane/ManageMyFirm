<?php

use App\controllers\CustomerController;
use Slim\App;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$app = new App($configuration);

$app->post('/customer', function ($request, $response){
    $data = $request->getParsedBody();
    $customerController = new CustomerController();
    $result = $customerController->addCustomer($data);
    return $response->withJson($result);
});

$app->put('/customer/{customer_id}', function ($request, $response){
    $customer_id = $request->getAttribute('customer_id');
    $data = $request->getParsedBody();
    $customerController = new CustomerController();
    $result = $customerController->updateCustomer($customer_id, $data);
    return $response->withJson($result);
});

$app->get('/customer/{customer_id}', function ($request, $response){
    $customer_id = $request->getAttribute('customer_id');
    $customerController = new CustomerController();
    $result = $customerController->getCustomer($customer_id);
    return $response->withJson($result);
});
$app->get('/customer', function ($request, $response){
    $searchCriteria = $request->getParam('data');
    $searchCriteria = json_decode($searchCriteria, 1);
    $customerController = new CustomerController();
    $result = $customerController->getCustomers($searchCriteria);
    return $response->withJson($result);
});

$app->delete('/customer/{customer_id}', function ($request, $response){
    $customer_id = $request->getAttribute('customer_id');
    $customerController = new CustomerController();
    $result = $customerController->deleteCustomer($customer_id);
    return $response->withJson($result);
});

$app->get('/customers/search', function ($request, $response){
    $data = $request->getParam('data');
    $customerController = new CustomerController();
    $result = $customerController->searchCustomers($data);
    return $response->withJson($result);
});
$app->run();
