<?php


namespace App\controllers;


use App\providers\ActionDataProvider;
use App\providers\CompanyDataProvider;
use App\providers\CounterDataProvider;
use App\providers\CustomerDataProvider;
use App\util\BaseDataProvider;
use MongoDB\BSON\ObjectId;

class ActionController
{
    public function addAction(&$data){
        $actionDataProvider = new ActionDataProvider();
        $counter = $this->getCounter();
        $latestCounter = $counter + 1;

        $data['action_id'] = $latestCounter;
        $result = $actionDataProvider->insertOne($data);

        if(!$result) {
            return [
                'status' => 'failed',
                'message' => 'There is problem in inserting data'
            ];
        }

        $this->updateCounter($latestCounter);

        return[
            'status' => 'success',
            'message' => 'Action created successfully'
        ];
    }

    public function updateAction($actionId, $data){
        $actionDataProvider = new ActionDataProvider();
        $searchArray = ['_id' => new ObjectId($actionId)];
        $updateArray = ['$set' => $data];
        $result = $actionDataProvider->updateOne($searchArray, $updateArray);

        if($result == 0) {
            return [
                'status' => 'failed',
                'message' => 'Please provide valid ID or Data'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Action updated successfully'
        ];

    }

    public function getAction($actionId){
        $actionDataProvider = new ActionDataProvider();
        $searchArray = ['_id' => new ObjectId($actionId)];
        $action = $actionDataProvider->findOne($searchArray);

        $customerAndCompanyName = $this->getCustomerAndCompany($action['customer_id']);
        $customerName = $customerAndCompanyName['customerFullName'];
        $companyName = $customerAndCompanyName['companyName'];

        unset($action['customer_id']);
        $action['customer_name'] = $customerName;
        $action['company_name'] = $companyName;

        return $action;
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
        $searchArray = $data;
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
        $projection = ['_id' => 0, 'company_id' => 1,
            'first_name' => 1, 'middle_name' => 1, 'last_name' => 1];
        $customer = $customerDataProvider->findOne($searchArray, $projection);
        $customerFullName = $customer['first_name']  . ' ' . $customer['middle_name'] . ' '. $customer['last_name'];
        $companyName = $this->getCompanyName($customer['company_id']);

        return ['customerFullName' => $customerFullName, 'companyName' => $companyName];
    }

    private function getCompanyName($companyId){
        $companyDataProvider = new CompanyDataProvider();
        $searchArray = ['_id' => new ObjectId($companyId)];
        $projection = ['_id' => 0, 'name' => 1];
        $company = $companyDataProvider->findOne($searchArray, $projection);
        return $company['name'];
    }

}