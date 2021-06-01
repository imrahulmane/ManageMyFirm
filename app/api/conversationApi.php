<?php

use App\controllers\ConversationController;
use Slim\App;

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$app = new App($configuration);

$app->post('/conversation', function ($request, $response){
    $data = $request->getParsedBody();
    $conversationController = new ConversationController();
    $result = $conversationController->addConversation($data);
    return $response->withJson($result);

});

$app->put('/conversation/{conversation_id}', function ($request, $response){
    $conversationId = $request->getAttribute('conversation_id');
    $data = $request->getParsedBody();
    $conversationController = new ConversationController();
    $result = $conversationController->updateConversation($conversationId, $data);
    return $response->withJson($result);
});

$app->get('/conversation/{conversation_id}', function ($request, $response){
    $conversationId = $request->getAttribute('conversation_id');
    $conversationController = new ConversationController();
    $result = $conversationController->getConversation($conversationId);
    return $response->withJson($result);
});

$app->get('/conversation', function ($request, $response){
    $searchCriteria = $request->getParam('data');
    $searchCriteria = json_decode($searchCriteria, 1);
    $conversationController = new ConversationController();
    $result = $conversationController->getAllConversation($searchCriteria);
    return $response->withJson($result);
});

$app->delete('/conversation/{conversation_id}', function ($request, $response){
    $conversationId = $request->getAttribute('conversation_id');
    $conversationController = new ConversationController();
    $result = $conversationController->deleteConversation($conversationId);
    return $response->withJson($result);
});

$app->patch('/conversation/{conversation_id}', function ($request, $response){
    $conversationId = $request->getAttribute('conversation_id');
    $conversationController = new ConversationController();
    $result = $conversationController->closeConversation($conversationId);
    return $response->withJson($result);
});

$app->get('/conversations/search', function ($request, $response){
    $data = $request->getParam('data');
    $conversationController = new ConversationController();
    $result = $conversationController->searchConversations($data);
    return $response->withJson($result);
});


//$app->post('/', function ($request, $response){});


$app->run();
