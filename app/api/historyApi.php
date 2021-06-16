<?php

use App\controllers\HistoryController;

require __DIR__ . '/../util/slim.php';

$app->get('/history', function ($request, $response){
    $searchCriteria = $request->getParam('search_criteria');
    $searchCriteria = json_decode($searchCriteria, 1);
    $historyController = new HistoryController();
    $result = $historyController->getHistory($searchCriteria);
    return $response->withJson($result);
});


$app->run();