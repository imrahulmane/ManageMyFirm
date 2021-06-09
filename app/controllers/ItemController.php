<?php


namespace App\controllers;

use App\providers\ItemDataProvider;
use App\providers\ServiceDataProvider;
use App\validators\ItemValidator;
use MongoDB\BSON\ObjectId;

class ItemController
{
    public function addItem($data){
        //validated incoming data
        $this->validateData($data, 'add');

        //insert data into collection
        $itemDataProvider = new ItemDataProvider();
        $data['cost_per_hr'] = (int) $data['cost_per_hr']; //convert to int
        $result = $itemDataProvider->insertOne($data);

        if(!$result) {
            //return false if data is not inserted
            return [
                'status' => 'failed',
                'message' => 'There is problem in inserting data'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Successfully added item'
        ];
    }

    public function updateItem($itemId, $data) {
        //validate data
        $this->validateData($data, 'update');

        //TODO: check cost is changing, if yes then change in service collection also [only if status is active]

        //update data
        $itemDataProvider = new ItemDataProvider();
        $searchArray = ['_id' => new ObjectId($itemId)];
        $updateArray = ['$set' => $data];

        $itemDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => 'Item is updated successfully'
        ];
    }

    public function getItem($itemId) {
        $itemDataProvider = new ItemDataProvider();
        $searchArray = ['_id' => new ObjectId($itemId)];

        //fetch item
        $item = $itemDataProvider->findOne($searchArray);

        //return failed message is item is not present
        if($item == false) {
            return [
                'status' => 'failed',
                'message' => 'Invalid Item ID'
            ];
        }

        return $item;
    }

    public function getAllItems($searchCriteria){
        $itemDataProvider = new ItemDataProvider();
        $searchArray = [];

        if(!empty($searchCriteria)){
            $searchArray = $searchCriteria;
        }

        $items = $itemDataProvider->find($searchArray);

        if($items == false) {
            return [
                'status' => 'failed',
                'message' => 'There are no items present in DB'
            ];
        }

        return $items;
    }
    public function deleteItem($itemId){
        $itemDataProvider = new ItemDataProvider();
        $searchArray = ['_id' => new ObjectId($itemId)];

        $isDeleted = $itemDataProvider->deleteOne($searchArray);

        //return fail message if delete count is zero
        if($isDeleted == 0) {
            return [
                'status' => 'failed',
                'message' => 'invalid ItemID'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Item Deleted Successfully'
        ];
     }

     public function searchItem($searchCriteria) {
        $itemDataProvider = new ItemDataProvider();
        $searchArray = ['type' => $searchCriteria];
        $item = $itemDataProvider->findOne($searchArray);

        if($item == false) {
            return [
                'status' => 'false',
                'message' => "couldn't find out item"
            ];
        }

        return $item;
     }

    //utility functions
    private function validateData($data, $scenario) {
        $itemValidator = new ItemValidator($data, $scenario);
        return $itemValidator->validate();
    }
}