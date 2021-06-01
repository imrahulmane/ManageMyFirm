<?php


namespace App\controllers;


use App\providers\CompanyDataProvider;
use App\providers\CustomerDataProvider;
use App\util\BaseDataProvider;
use http\Exception\BadHeaderException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;

class CustomerController
{
    public function addCustomer($data) {
        $customerDataProvider = new CustomerDataProvider();
        $email = $data['email'];
        $searchArray  = ['email' => $email];
        $isEmailExist = $customerDataProvider->findOne($searchArray);

        if($isEmailExist != false){
            return[
                'status' => 'failed',
                'message' => 'Email address is already taken.'
            ];
        }

        $result = $customerDataProvider->insertOne($data);

        if(!$result) {
            return [
                'status' => 'failed',
                'message' => 'There is problem inserting a customer'
            ];
        }
        return [
            'status' => 'success',
            'message' => 'Customer added Successfully'
        ];

    }

    public function updateCustomer($customer_id, $data){
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = ['_id' => new ObjectId($customer_id)];
        $updateArray = ['$set' => $data];
        $result = $customerDataProvider->updateOne($searchArray, $updateArray);

        if($result == 0) {
            return [
                'status' => 'failed',
                'message' => 'Customer id is invalid'
            ];
        }

        return[
          'status' => 'success',
          'message' => 'Customer updated successfully'
        ];
    }

    public function getCustomer($customer_id) {
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = ['_id'=> new ObjectId($customer_id)];
        $customer = $customerDataProvider->findOne($searchArray);

        $company = $this->getCompany($customer['company_id']);
        unset($customer['company_id']);
        $customer['company_name'] = $company['name'];

        if($customer == false) {
            return [
                'status' => 'failed',
                'message' => 'Customer id is invalid'
            ];
        }

        return $customer;

    }

    public function getCustomers() {
        $customerDataProvider = new CustomerDataProvider();
        $customers = $customerDataProvider->find();

        if(empty($customers)) {
            return [
                'status' => 'failed',
                'message' => 'Customers not found'
            ];
        }

        foreach ($customers as $key => $customer) {
            $company = $this->getCompany($customer['company_id']);
            unset($customers[$key]['company_id']);
            $customers[$key]['company_name'] = $company['name'];
        }

        return $customers;

    }

    public function deleteCustomer($customer_id){
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = ['_id' => new ObjectId($customer_id)];

        $result = $customerDataProvider->deleteOne($searchArray);

        if($result == 0) {
            return[
                'status' => 'failed',
                'message' => 'Please provide valid customer ID'
            ];
        }

        return[
            'status' => 'success',
            'message' => 'Customer deleted successfully.'
        ];
    }

    public function searchCustomers($data){
        $customerDataProvider = new CustomerDataProvider();
        $regex = ['$regex' => new Regex("^$data", 'i')];
        $searchArray = ['$or' =>
        [
            ['first_name' => $regex],
            ['middle_name'=> $regex],
            ['last_name' => $regex]
        ]
        ];

        $customers = $customerDataProvider->find($searchArray);
        return $customers;
    }

    private function getCompany($company_id){
        $searchArray = ['_id' => new ObjectId($company_id)];
        $projection = ['_id' => 0, 'name' => 1];

        $companyDataProvider = new CompanyDataProvider();
        $company = $companyDataProvider->findOne($searchArray, $projection);

        if($company == false) {
            return [
                'status' => 'failed',
                'message' => "Couldn't find company name with the company ID"
            ];
        }

        return $company;
    }
}