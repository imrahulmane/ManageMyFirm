<?php


namespace App\controllers;

require __DIR__ . '/../util/constants.php';
require __DIR__ .'/../helpers/saveImage.php';

use App\providers\CompanyDataProvider;
use App\providers\CustomerDataProvider;
use App\providers\HistoryDataProvider;
use App\validators\CompanyValidator;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;


class CompanyController
{

    public function addCompany($data)
    {
        $validator = new CompanyValidator($data, 'add');
        $validator->validate();

        $companyDataProvider = new CompanyDataProvider();
        $isEmailExist = $this->checkEmailExist($data['support_email']);

        if($isEmailExist) {
            return [
                "status" => "failed",
                "message" => "Email Address is already exist"
            ];
        }

        $result = $companyDataProvider->insertOne($data);

        if(!$result) {
            return[
                "status" => "failed",
                "message" => "There's problem in inserting data"
            ];
        }

        return [
            "status" => "success",
            "message" => "Company added successfully"
        ];
    }


    public function updateCompany($companyId, $data) {
        $validator = new CompanyValidator($data, 'update');
        $validator->validate();

        $companyDataProvider = new CompanyDataProvider();

        if($data['support_email']) {
            $isEmailExist = $this->checkEmailExist($data['support_email'], $companyId);

        if($isEmailExist) {
                return [
                    "status" => "failed",
                    "message" => "Email Address is already exist"
                ];
            }
        }

        $searchArray = ['_id' => new ObjectId($companyId)];
        $updateArray = ['$set' => $data];
        $companyDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => 'Company updated successfully'
        ];
    }

    public function getCompany($company_id){
        $companyDataProvider = new CompanyDataProvider();
        $searchArray = ['_id' => new ObjectId($company_id)];
        $result = $companyDataProvider->findOne($searchArray);

        saveImageToFolder("https://picsum.photos/id/1/200/300", 'f.png');


        if($result == false){
            return[
                'status' => 'failed',
                'message' => 'Please provide valid company ID'
            ];
        }

        return $result;
    }

    public function getCompanies(){
        $companyDataProvider = new CompanyDataProvider();
        $companies = $companyDataProvider->find();

        if($companies == false){
            return[
                'status' => 'failed',
                'message' => 'There are no companies right now'
            ];
        }

        foreach($companies as $key => $company) {
            $customerCount = $this->getCustomerCountOfEachCompany($company['_id']);
            $companies[$key]['Customer Count'] = $customerCount;
        }

        return $companies;
    }

    public function deleteCompany($company_id){
        $companyDataProvider = new CompanyDataProvider();
        $searchArray = ['_id' => new ObjectId($company_id)];

        $result = $companyDataProvider->deleteOne($searchArray);

        if($result === 0 ){
            return[
                'status' => 'failed',
                'message' => 'Please provide valid company ID'
            ];
        }

        return[
            'status' => 'success',
            'message' => 'Company Deleted Successfully'
        ];

    }

    public function searchCompanies($data){
        //save history
        $historyDataProvider =  new HistoryDataProvider();
        $insertHistory = [
            'search' => $data,
            'type' => COMPANY_TYPE
        ];
        $historyDataProvider->insertOne($insertHistory);

        $companyDataProvider = new CompanyDataProvider();
        $regex = ['$regex' => new Regex("^$data" ,"i")];
        $searchArray = [
            '$or' => [
                ['name' => $regex],
                ['support_email' => $regex]
            ]
        ];
        $options = ['projection' => ['_id' => 0 ,'name' => 1, 'support_email' => 1] ];
        $companies = $companyDataProvider->find($searchArray, $options);

        if($companies == false){
            return [
                'status' => 'failed',
                'message' => 'please provide valid data'
            ];
        }

        $result = [];

        foreach ($companies as $company) {
            $result [] = array_values(preg_grep("/^$data/i", $company));
        }

        $result = array_merge(...$result);

        return $result;
    }

    //utility methods
    private function checkEmailExist($email, $companyId=false) {
        $companyDataProvider = new CompanyDataProvider();
        $emailSearchArray = ['support_email' => $email];

        if($companyId){
            $searchArray = ['_id' => ['$ne' => new ObjectId($companyId)]];
            $emailSearchArray = array_merge($searchArray, $emailSearchArray);
        }

        $isEmailExist = $companyDataProvider->findOne($emailSearchArray);

        if($isEmailExist) {
            return true;
        }

        return false;
    }

    private function getCustomerCountOfEachCompany($company_id) {
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = ['company_id' => (string) $company_id];
        $customerCount =  $customerDataProvider->recordCount($searchArray);
        return $customerCount;
    }
}