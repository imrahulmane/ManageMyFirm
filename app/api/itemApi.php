<?php

use App\controllers\ItemController;

require __DIR__ . '/../util/slim.php';


$app->post('/item', function ($request, $response){
    $data = $request->getParsedBody();
    $itemController = new ItemController();
    $result = $itemController->addItem($data);
    return $response->withJson($result);
});

$app->put('/item/{item_id}', function ($request, $response){
    $itemId = $request->getAttribute('item_id');
    $data = $request->getParsedBody();
    $itemController = new ItemController();
    $result = $itemController->updateItem($itemId, $data);
    return $response->withJson($result);
});

$app->get('/item/{item_id}', function ($request, $response){
    $itemId = $request->getAttribute('item_id');
    $itemController = new ItemController();
    $result = $itemController->getItem($itemId);
    return $response->withJson($result);
});

$app->get('/item', function ($request, $response){
    $itemController = new ItemController();
    $result = $itemController->getAllItems();
    return $response->withJson($result);
});

$app->delete('/item/{item_id}', function ($request, $response){
    $itemId = $request->getAttribute('item_id');
    $itemController = new ItemController();
    $result = $itemController->deleteItem($itemId);
    return $response->withJson($result);
});

//$app->post('item', function ($request, $response){});


$app -> run();