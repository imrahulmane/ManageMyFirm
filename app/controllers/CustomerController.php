<?php


namespace App\controllers;
use App\providers\CompanyDataProvider;
use App\providers\CustomerDataProvider;
use App\util\BaseDataProvider;
use App\validators\CustomerValidator;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use App\providers\HistoryDataProvider;
require __DIR__ . '/../util/constants.php';
require __DIR__ . '/../helpers/saveImage.php';

class CustomerController
{
    public function addCustomer($data, $file) {
        $validator = new CustomerValidator($data, 'add');
        $validator->validate();

        $isEmailExist = $this->checkEmailExist($data['email']);

        if($isEmailExist){
            return[
                'status' => 'failed',
                'message' => 'Email address is already taken.'
            ];
        }

        //store image in public folder
        $filePath = moveUploadedFile($file['image'], $data['email']);

        $data['img_url'] = $filePath; //add filepath to customer schema
        $data['status'] = 'active';  //add status of customer

        $customerDataProvider = new CustomerDataProvider();
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
        $validator = new CustomerValidator($data, 'update');
        $validator->validate();

        $customerDataProvider = new CustomerDataProvider();

        if($data['email']) {
            $isEmailExist = $this->checkEmailExist($data['email'], $customer_id);

            if($isEmailExist){
                return[
                    'status' => 'failed',
                    'message' => 'Email address is already taken.'
                ];
            }
        }

        $searchArray = ['_id' => new ObjectId($customer_id)];
        $updateArray = ['$set' => $data];
        $customerDataProvider->updateOne($searchArray, $updateArray);

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

    public function getCustomers($searchCriteria) {
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = [];

        if(!empty($searchCriteria)) {
            $searchArray = $searchCriteria;
        }

        $customers = $customerDataProvider->find($searchArray);

        if(empty($customers)) {
            return [
                'status' => 'failed',
                'message' => 'Customers not found'
            ];
        }

        foreach ($customers as $key => $customer) {
            $company = $this->getCompany($customer['company_id']);
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

        //add to history collection
        $historyDataProvider =  new HistoryDataProvider();
        $insertHistory = [
            'search' => $data,
            'type' => CUSTOMER_TYPE
        ];
        $historyDataProvider->insertOne($insertHistory);


        $customerDataProvider = new CustomerDataProvider();
        $regex = ['$regex' => new Regex("^$data", 'i')];
        $searchArray = ['$or' =>
        [
            ['first_name' => $regex],
            ['middle_name'=> $regex],
            ['last_name' => $regex],
            ['description' => $regex]
        ]
        ];

        $options = ['projection' => ['_id' => 0,  'first_name' => 1, 'middle_name' => 1 ,'last_name' => 1, 'description' => 1]];
        $customers = $customerDataProvider->find($searchArray, $options);

        if(empty($customers)) {
            return [
                'status' => 'failed',
                'message' => 'Customers not found'
            ];
        }

        $result = [];

        foreach ($customers as $customer){
            $result [] = array_values(preg_grep("/^$data/i", $customer));
        }

        $result = array_merge(...$result);
        return $result;
    }

    //utility functions
    private function getCompany($company_id){
        $searchArray = ['_id' => new ObjectId($company_id)];
        $options = ['projection' => ['_id' => 0, 'name' => 1]];

        $companyDataProvider = new CompanyDataProvider();
        $company = $companyDataProvider->findOne($searchArray, $options);

        if($company == false) {
            return [
                'status' => 'failed',
                'message' => "Couldn't find company name with the company ID"
            ];
        }

        return $company;
    }

    private function checkEmailExist($email, $customerId=false) {
        $customerDataProvider = new CustomerDataProvider();
        $emailSearchArray = ['email' => $email];

        if($customerId){
            $searchArray = ['_id' => ['$ne' => new ObjectId($customerId)]];
            $emailSearchArray = array_merge($searchArray, $emailSearchArray);
        }

        $isEmailExist = $customerDataProvider->findOne($emailSearchArray);

        if($isEmailExist) {
            return true;
        }
        return false;
    }

    public function updateProfileImage($customerId, $file)
    {
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = ['_id' => new ObjectId($customerId)];
        $customer = $customerDataProvider->findOne($searchArray);

        if($customer == false) {
            return [
                'status' => 'failed',
                'messagae' => 'There is no customer with this customer ID'
            ];
        }

        $imageName =$customer['email']; // assign image name as email of the customer
//        dd($file['image']);
        $imagePath = moveUploadedFile($file['image'], $imageName);

        $customer['img_url'] = $imagePath;
        $updateArray = ['$set' => $customer];
        $customerDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => 'Profile Image updated successfully'
        ];

    }

    public function deleteProfileImage($customer_id)
    {

    }
}