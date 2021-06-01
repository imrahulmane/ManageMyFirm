<?php


namespace App\controllers;


use App\providers\ConversationDataProvider;
use App\providers\CounterDataProvider;
use App\util\BaseDataProvider;
use MongoDB\BSON\ObjectId;

class ConversationController
{
    public function addConversation(&$data) {
        $conversationDataProvider = new ConversationDataProvider();
        $counter = $this->getCounter();
        $latestCounter = $counter + 1;

        $data['conv_id'] = $latestCounter;
        $result = $conversationDataProvider->insertOne($data);

        if(!$result) {
            return[
                'status'=> 'failed',
                'message'=> 'Failed to insert Data'
            ];
        }

        $this->updateCounter($latestCounter);

        return [
            'status' => 'success',
            'message' => 'Conversation Added Successfully'
        ];
    }

    public function updateConversation($conversationId, $data) {
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = ['_id' => new ObjectId($conversationId)];
        $updateArray = ['$set' => $data];

        $result = $conversationDataProvider->updateOne($searchArray, $updateArray);

        if($result == 0) {
            return [
                'status' => 'failed',
                'message' => 'Please Provide Valid ID or Data'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Conversation updated successfully'
        ];

    }

    public function getConversation($conversationId){
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = ['_id' => new ObjectId($conversationId)];

        $result = $conversationDataProvider->findOne($searchArray);

        if($result == false) {
            return [
                'status' => 'failed',
                'message' => 'Please Provide Valid ID'
            ];
        }
        return $result;
    }

    public function getAllConversation($searchCriteria){
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = $searchCriteria;

        $result = $conversationDataProvider->find($searchArray);

        if($result == false) {
            return [
                'status' => 'failed',
                'message' => 'Please Provide Valid ID'
            ];
        }
        return $result;
    }

    public function deleteConversation($conversationId){
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = ['_id' => new ObjectId($conversationId)];

        $result = $conversationDataProvider->deleteOne($searchArray);

        if($result == 0) {
            return [
                'status' => 'failed',
                'message' => 'Please provide valid conversation ID'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Conversation deleted successfully'
        ];
    }

    public function closeConversation($conversationId){
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = ['_id' => new ObjectId($conversationId)];
        $updateArray = ['$set' => ['status' => 'close']];
        $result = $conversationDataProvider->updateOne($searchArray, $updateArray);

        if($result == 0) {
            return [
                'status' => 'failed',
                'message' => 'Please provide valid conversation ID'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Conversation closed successfully'
        ];

    }

    public function searchConversations($data) {
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = ['conv_id' => (int) $data];
        $result = $conversationDataProvider->find($searchArray);

        if($result == false) {
            return [
                'status' => 'failed',
                'message' => 'There are no conversation with this ID'
            ];
        }
        return $result;

    }

    private function getCounter() {
        $counterDataProvider = new CounterDataProvider();
        $searchArray = ['type' => 1];
        $counterObject = $counterDataProvider->findOne($searchArray);
        return $counterObject['counter'];
    }

    private function updateCounter($latestCounter) {
        $counterDataProvider = new CounterDataProvider();
        $searchArray = ['type' => 1];
        $updateArray = ['$set' => ['counter' => $latestCounter]];
        $counterDataProvider->updateOne($searchArray, $updateArray);
    }


}