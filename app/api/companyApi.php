<?php

use App\controllers\CompanyController;
use Slim\App;

$configuration = [
'settings' => [
'displayErrorDetails' => true,
    ],
];

$app = new App($configuration);

$app->post('/company', function ($request, $response){
    $data = $request->getParsedBody();
    $companyController = new CompanyController();
    $result =  $companyController->addCompany($data);
    return $response->withJson($result);

});

$app->put('/company/{company_id}', function ($request, $response){
    $companyId = $request->getAttribute('company_id');
    $data = $request->getParsedBody();
    $companyController = new CompanyController();
    $result = $companyController->updateCompany($companyId, $data);
    return $response->withJson($result);
});

$app->get('/company/{company_id}', function ($request, $response){
    $companyId = $request->getAttribute('company_id');
    $companyController = new CompanyController();
    $result = $companyController->getCompany($companyId);
    return $response->withJson($result);
});

$app->get('/company', function ($request, $response){
    $companyController = new CompanyController();
    $result = $companyController->getCompanies();
    return $response->withJson($result);
});

$app->delete('/company/{company_id}', function ($request, $response){
    $companyId = $request->getAttribute('company_id');
    $companyController = new CompanyController();
    $result = $companyController->deleteCompany($companyId);
    return $response->withJson($result);
});

$app->get('/companies/search', function ($request, $response){
    $data = $request->getParam('data');
    $companyController = new CompanyController();
    $result = $companyController->searchCompanies($data);
    return $response->withJson($result);
});


$app->run();
