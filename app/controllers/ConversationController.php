<?php


namespace App\controllers;


use App\helpers\TagService;
use App\providers\ConversationDataProvider;
use App\providers\CounterDataProvider;
use App\providers\CustomerDataProvider;
use App\util\BaseDataProvider;
use App\validators\ConversationValidator;
use MongoDB\BSON\ObjectId;

class ConversationController
{
    public function addConversation(&$data) {
        $validator = new ConversationValidator($data, 'add');
        $validator->validate();

        $conversationDataProvider = new ConversationDataProvider();

        $counter = $this->getCounter();
        $latestCounter = $counter + 1;

        $data['conv_id'] = $latestCounter;

        //remove tags from $data
        if($data['tags']) {
            $tags = $data['tags'];
            $tags = explode(" ", $tags);
            unset($data['tags']);
        }

        $conversationId = $conversationDataProvider->insertOne($data);

        //add system tags and custom tags
        $tagService = new TagService();
        if($tags) {
            $tagService->addTags($conversationDataProvider, $conversationId, $data['conversation_message'], 'conversation', $tags);
        }
        $tagService->addTags($conversationDataProvider, $conversationId, $data['conversation_message'], 'conversation');


        if(!$conversationId) {
            return[
                'status'=> 'failed',
                'message'=> 'Failed to insert Data'
            ];
        }

        $this->updateCounter($latestCounter);
        $this->setStatusOfCustomer($data['customer_id'], 'conversation');

        return [
            'status' => 'success',
            'message' => 'Conversation Added Successfully'
        ];
    }

    public function updateConversation($conversationId, $data) {
        $validator = new ConversationValidator($data, 'update');
        $validator->validate();

        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = ['_id' => new ObjectId($conversationId)];
        $updateArray = ['$set' => $data];

        $conversationDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => 'Conversation updated successfully'
        ];

    }

    public function getConversation($customerID){
        $conversationDataProvider = new ConversationDataProvider();
        $search = ['customer_id' => $customerID];
        $options = ['sort' => ['reminder_date' => -1]];

        $conversations = $conversationDataProvider->find($search, $options);

        foreach ($conversations as $key => $conversation){
            //get tag names
            if($conversation['system_tags']) {
                $systemTagNames = TagService::getTagNames($conversation['system_tags']);
                $conversations[$key]['system_tags'] = $systemTagNames;
            }

            if($conversation['tags']){
                $customTagNames = TagService::getTagNames($conversation['tags']);
                $conversations[$key]['tags'] = $customTagNames;
            }
        }

        if($conversations == false) {
            return [
                'status' => 'failed',
                'message' => 'Please Provide Valid ID'
            ];
        }
        return $conversations;
    }

    public function getAllConversation($searchCriteria){
        $conversationDataProvider = new ConversationDataProvider();
        $searchArray = $searchCriteria;
        if(is_null($searchCriteria)){
            $searchArray = [];
        }

        $conversations = $conversationDataProvider->find($searchArray);

        foreach ($conversations as $key => $conversation){
            //get tag names
            if($conversation['system_tags']) {
                $systemTagNames = TagService::getTagNames($conversation['system_tags']);
                $conversations[$key]['system_tags'] = $systemTagNames;
            }

            if($conversation['tags']){
                $customTagNames = TagService::getTagNames($conversation['tags']);
                $conversations[$key]['tags'] = $customTagNames;
            }
        }

        if($conversations == false) {
            return [
                'status' => 'failed',
                'message' => 'Please Provide Valid ID'
            ];
        }
        return $conversations;
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
        $searchArray = [];

        if(!empty($data)){
            $searchArray = ['conv_id' => (int) $data];
        }

        $result = $conversationDataProvider->find($searchArray);

        if($result == false) {
            return [
                'status' => 'failed',
                'message' => 'There are no conversation with this ID'
            ];
        }
        return $result;

    }

    //utility functions
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


    private function setStatusOfCustomer($customer_id, $status){
        $customerDataProvider = new CustomerDataProvider();
        $searchArray  = ['_id' => new ObjectId($customer_id)];
        $updateArray = ['$set' => ['status' => $status]];
        $isUpdated = $customerDataProvider->updateOne($searchArray, $updateArray);

        if($isUpdated == 0){
            return [
                'status' => 'failed',
                'message' => 'there is problem setting status to action in customer'
            ];
        }
    }
}