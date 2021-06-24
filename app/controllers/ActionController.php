<?php


namespace App\controllers;


use App\helpers\TagCRUD;
use App\providers\ActionDataProvider;
use App\providers\CompanyDataProvider;
use App\providers\CounterDataProvider;
use App\providers\CustomerDataProvider;
use App\util\BaseDataProvider;
use App\validators\ActionValidator;
use MongoDB\BSON\ObjectId;

class ActionController
{
    public function addAction(&$data){
        $validator = new ActionValidator($data, 'add');
        $validator->validate();

        $actionDataProvider = new ActionDataProvider();
        $counter = $this->getCounter();
        $latestCounter = $counter + 1;

        $data['action_id'] = $latestCounter; //add action_id

        //remove tags from $data
        if($data['tags']) {
            $tags = $data['tags'];
            $tags = explode(" ", $tags);
            unset($data['tags']);
        }

        $actionId = $actionDataProvider->insertOne($data);

        //add system tags and custom tags
        if($tags) {
            $this->addTags($actionId, $data['action_message'], $tags);
        } else {
            $this->addTags($actionId, $data['action_message']);
        }

        if(!$actionId) {
            return [
                'status' => 'failed',
                'message' => 'There is problem in inserting data'
            ];
        }

        $this->updateCounter($latestCounter);
        $this->setStatusOfCustomer($data['customer_id'], 'action');

        return[
            'status' => 'success',
            'message' => 'Action created successfully'
        ];
    }

    private function addTags($actionId, $name, $tags = []) {
        //add system tag to the customer
        $tagsHelper = new TagCRUD();
        $systemTagIds = $tagsHelper->addSystemTag($actionId, $name, 'action');

        if(!empty($tags)){
            $tagIds = $tagsHelper->addCustomTags($actionId, $tags, 'action');
        }

        $actionDataProvider = new ActionDataProvider();
        $searchArray = ['_id' => new ObjectId($actionId)];

        $action = $actionDataProvider->findOne($searchArray);

        if(!empty($tags)){
            $action['system_tags'] = $systemTagIds;
            $action['tags'] = $tagIds;
        } else {
            $action['system_tags'] = (string) $systemTagIds;
        }

        $updateArray = ['$set' => $action];
        $actionDataProvider->updateOne($searchArray, $updateArray);

    }

    public function updateAction($actionId, $data){
        $validator = new ActionValidator($data, 'update');
        $validator->validate();

        $actionDataProvider = new ActionDataProvider();
        $searchArray = ['_id' => new ObjectId($actionId)];
        $updateArray = ['$set' => $data];
        $actionDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => 'Action updated successfully'
        ];

    }

    public function getAction($customerId){
        $actionDataProvider = new ActionDataProvider();
        $search = ['customer_id' => (string) ($customerId)];

        $currentDate = date('d-m-y');
        $options = ['action_date' => ['$gt' => $currentDate]];
        $searchArray = array_merge($search, $options);
        $actions = $actionDataProvider->find($searchArray);

        foreach ($actions as $key => $action) {
            $customerAndCompanyName = $this->getCustomerAndCompany($action['customer_id']);
            $customerName = $customerAndCompanyName['customerFullName'];
            $companyName = $customerAndCompanyName['companyName'];

            $actions[$key]['customer_name'] = $customerName;
            $actions[$key]['company_name'] = $companyName;
        }

        return $actions;
    }

    public function getAllActions(){
        $actionDataProvider = new ActionDataProvider();
        $actions = $actionDataProvider->find();

        if(empty($actions)) {
            return [
              'status' => 'failed',
              'message' => 'There are no Actions available'
            ];
        }

        foreach ($actions as $key => $action) {
            $customerAndCompanyName = $this->getCustomerAndCompany($action['customer_id']);
            $customerName = $customerAndCompanyName['customerFullName'];
            $companyName = $customerAndCompanyName['companyName'];

            unset($actions[$key]['customer_id']);
            $actions[$key]['customer_name'] = $customerName;
            $actions[$key]['company_name'] = $companyName;
        }

        return $actions;
    }
    public function deleteAction($actionId){
        $actionDataProvider = new ActionDataProvider();
        $searchArray = ['_id' => new ObjectId($actionId)];
        $isDeleted = $actionDataProvider->deleteOne($searchArray);

        if($isDeleted == 0) {
            return[
                'status' => 'failed',
                'message' => 'Please provide valid ID'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Action is deleted successfully'
        ];

    }
    public function closeAction($actionId){
        $actionDataProvider = new ActionDataProvider();
        $searchArray = ['_id' => new ObjectId($actionId)];
        $updateArray = ['$set' => ['status' => 'close']];
        $isUpdated = $actionDataProvider->updateOne($searchArray, $updateArray);

        if($isUpdated == 0) {
            return[
                'status' => 'failed',
                'message' => 'Please provide valid ID'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Action is closed successfully'
        ];
    }

    public function searchAction($data){
        $actionDataProvider = new ActionDataProvider();
        $searchArray = [];

        if($data != null) {
            $searchArray = ['action_id' => $data];
        }

        $result = $actionDataProvider->find($searchArray);

        if($result == false) {
            return[
                'status' => 'failed',
                'message' => 'Please provide valid data to search'
            ];
        }

        return $result;
    }

    private function getCounter(){
        $counterDataProvider = new CounterDataProvider();
        $searchArray = ['type' => 2];
        $counterObject = $counterDataProvider->findOne($searchArray);
        return $counterObject['counter'];
    }

    private function updateCounter($latestCounter){
        $counterDataProvider = new CounterDataProvider();
        $searchArray = ['type' => 2];
        $updateArray = ['$set' => ['counter' => $latestCounter]];
        $counterDataProvider->updateOne($searchArray, $updateArray);
    }

    private function getCustomerAndCompany($customerId){
        $customerDataProvider = new CustomerDataProvider();

        $searchArray = ['_id' => new ObjectId($customerId)];
        $options = ['projection' => ['_id' => 0, 'company_id' => 1,
            'first_name' => 1, 'middle_name' => 1, 'last_name' => 1]];

        $customer = $customerDataProvider->findOne($searchArray, $options);

        $customerFullName = $customer['first_name']  . ' ' . $customer['middle_name'] . ' '. $customer['last_name'];
        $companyName = $this->getCompanyName($customer['company_id']);

        return ['customerFullName' => $customerFullName, 'companyName' => $companyName];
    }

    private function getCompanyName($companyId){
        $companyDataProvider = new CompanyDataProvider();
        $searchArray = ['_id' => new ObjectId($companyId)];
        $options = ['projection' => ['_id' => 0, 'name' => 1]];
        $company = $companyDataProvider->findOne($searchArray, $options);
        return $company['name'];
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