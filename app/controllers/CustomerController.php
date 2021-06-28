<?php


namespace App\controllers;
use App\helpers\TagService;
use App\providers\CompanyDataProvider;
use App\providers\CustomerDataProvider;
use App\providers\TagDataProvider;
use App\util\ArrayUtil;
use App\util\BaseDataProvider;
use App\util\MongoUtil;
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

        if($data['tags']) {
            $tags = $data['tags'];
            $tags = explode(" ", $tags);
            unset($data['tags']);
        }

        $customerDataProvider = new CustomerDataProvider();
        $customerId = $customerDataProvider->insertOne($data);  //returns inserted customer id

        //add system tags and custom tags
        $tagService = new TagService();
        if($tags) {
            $tagService->addTags($customerDataProvider, $customerId, $data['first_name'], 'customer', $tags);
        }
        $tagService->addTags($customerDataProvider, $customerId, $data['first_name'], 'customer');

        if(!$customerId) {
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

        //get company name
        $companyDataProvider = new CompanyDataProvider();
        $searchArray = ['_id' => new ObjectId($customer['company_id'])];
        $options = ['projection' => ['_id' => 0, 'name' => 1]];
        $company_name = $companyDataProvider->findOne($searchArray, $options);
        unset($customer['company_id']);
        $customer['company_name'] = $company_name;

        //get tag names
        $systemTagNames = TagService::getTagNames($customer['system_tags']);
        $customer['system_tags'] = $systemTagNames;
        if($customer['tags']){
            $customTagNames = TagService::getTagNames($customer['tags']);
            $customer['tags'] = $customTagNames;
        }

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

        if(!empty($data)) {
            $searchArray = $searchCriteria;
        }

        $customers = $customerDataProvider->find($searchArray);

        if(empty($customers)) {
            return [
                'status' => 'failed',
                'message' => 'Customers not found'
            ];
        }

        $companies = $this->getCompanies(array_column($customers, 'company_id'));
        foreach ($customers as $key => $customer) {
            $customers[$key]['company_name'] = $companies[$customer['company_id']]['name'];

            //get tag names
            if($customer['system_tags']) {
                $systemTagNames = TagService::getTagNames($customer['system_tags']);
                $customers[$key]['system_tags'] = $systemTagNames;
            }

            if($customer['tags']){
                $customTagNames = TagService::getTagNames($customer['tags']);
                $customers[$key]['tags'] = $customTagNames;
            }
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

    public function suggestCustomers($data){

        //add to history collection
        $historyDataProvider =  new HistoryDataProvider();
        $insertHistory = [
            'search' => $data,
            'type' => CUSTOMER_TYPE
        ];
        $historyDataProvider->insertOne($insertHistory);

        //search customers
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

        //search tags
        $searchArrayTag = [ '$and' =>
            [
                ['module' => 'customer'],
                ['tag_name' => $regex]
            ]
        ];
        $optionsArrayTag = ['projection' => ['_id' => 0, 'tag_name' => 1]];
        $tagDataProvider = new TagDataProvider();
        $tagNames = $tagDataProvider->find($searchArrayTag, $optionsArrayTag);

        $customersAndTagNames = array_merge($customers, $tagNames);


        $result = [];
        foreach ($customersAndTagNames as $item){
            $result [] = array_values(preg_grep("/^$data/i", $item));
        }

        $result = array_merge(...$result);
        return $result;
    }

    public function searchCustomers($data){
        $regex = ['$regex' => new Regex("^$data", 'i')];  //for searching data

        //search tags
        $tagIds = TagService::getTagIds($regex);

        //search customers
        $customerDataProvider = new CustomerDataProvider();
        $customerSearchArray = ['$or' =>
            [
                ['first_name' => $regex],
                ['middle_name'=> $regex],
                ['last_name' => $regex],
            ]
        ];

        $searchArray = $customerSearchArray;
        if(!empty($tagIds)) {
            $searchArray = ['$or' =>
                [
                    ['$or' => $tagIds],
                    $customerSearchArray
                ]
            ];
        }

        $customers = $customerDataProvider->find($searchArray);

        foreach ($customers as $k => $customer){
            if($customer['tags'] && $customer['system_tags']){
                $customers[$k]['system_tags'] = TagService::getTagNames($customer['system_tags']);
                $customers[$k]['tags'] = TagService::getTagNames($customer['tags']);
            }
        }


        return $customers;
    }

    //utility functions
    private function getCompanies($companyIds){
        $companyIds = MongoUtil::convertStringIdToObjectId($companyIds);
        $searchArray = ['_id' => ['$in' => $companyIds]];
        $options = ['projection' => ['name' => 1]];

        $companyDataProvider = new CompanyDataProvider();
        $companies = $companyDataProvider->find($searchArray, $options);
        $companies = ArrayUtil::getKeyValueMap($companies);

        return $companies;
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

    public function deleteProfileImage($customerId)
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

        $image = $customer['img_url'];
        unlink($image);

        $customer['img_url'] = "";
        $updateArray = ['$set' => $customer];
        $customerDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => 'Profile Image deleted successfully'
        ];
    }
}



