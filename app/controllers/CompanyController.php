<?php


namespace App\controllers;


use App\providers\CompanyDataProvider;
use App\util\BaseDataProvider;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;


class CompanyController
{

    public function addCompany($data)
    {
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

        if($result == false){
            return[
                'status' => 'failed',
                'message' => 'Please provide valid company ID'
            ];
        }

        return $result;
    }

    public function getCompanies(){
        //TODO: add customers count in each company

        $companyDataProvider = new CompanyDataProvider();
        $result = $companyDataProvider->find();

        if($result == false){
            return[
                'status' => 'failed',
                'message' => 'There are no companies right now'
            ];
        }

        return $result;
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
        $companyDataProvider = new CompanyDataProvider();
        $regex = ['$regex' => new Regex("^$data" ,"i")];
        $searchArray = ['name' => $regex];
        $result = $companyDataProvider->find($searchArray);

        if($result == false){
            return [
                'status' => 'failed',
                'message' => 'please provide valid data'
            ];
        }

        return $result;
    }

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
}