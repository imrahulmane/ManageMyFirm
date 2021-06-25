<?php


namespace App\controllers;
require __DIR__ . '/../util/constants.php';

use App\helpers\TagService;
use App\providers\HistoryDataProvider;
use App\providers\ItemDataProvider;
use App\providers\ServiceDataProvider;
use App\util\BaseDataProvider;
use App\validators\ItemValidator;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;

class ItemController
{
    public function addItem($data){
        //validated incoming data
        $this->validateData($data, 'add');

        //insert data into collection
        $itemDataProvider = new ItemDataProvider();
        $data['cost_per_hr'] = (int) $data['cost_per_hr']; //convert to int

        //remove tags from $data
        if($data['tags']) {
            $tags = $data['tags'];
            $tags = explode(" ", $tags);
            unset($data['tags']);
        }

        $itemID = $itemDataProvider->insertOne($data);

        //add system tags and custom tags
        $tagService = new TagService();
        if($tags) {
            $tagService->addTags($itemDataProvider, $itemID, $data['type'], 'item', $tags);
        }
        $tagService->addTags($itemDataProvider, $itemID, $data['type'], 'item');


        if(!$itemID) {
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

        //get tag names
        if($item['system_tags']) {
            $systemTagNames = TagService::getTagNames($item['system_tags']);
            $item['system_tags'] = $systemTagNames;
        }

        if($item['tags']){
            $customTagNames = TagService::getTagNames($item['tags']);
            $item['tags'] = $customTagNames;
        }

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

        foreach ($items as $key => $item){
            //get tag names
            if($item['system_tags']) {
                $systemTagNames = TagService::getTagNames($item['system_tags']);
                $items[$key]['system_tags'] = $systemTagNames;
            }

            if($item['tags']){
                $customTagNames = TagService::getTagNames($item['tags']);
                $items[$key]['tags'] = $customTagNames;
            }
        }

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

        $historyDataProvider = new HistoryDataProvider();
        $insertHistory = [
            'search' => $searchCriteria,
            'type' => ITEM_TYPE
        ];

        $historyDataProvider->insertOne($insertHistory);

        $itemDataProvider = new ItemDataProvider();
        $regex = ['$regex' => new Regex("^$searchCriteria", "i")];
        $searchArray = ['type' => $regex];
        $options = ['projection' => ['_id' => 0, 'type' => 1]];
        $items = $itemDataProvider->find($searchArray, $options);

        if($items == false) {
            return [
                'status' => 'false',
                'message' => "couldn't find out item"
            ];
        }

        $result = [];

         foreach ($items as $item) {
             $result [] = array_values(preg_grep("/^$searchCriteria/i", $item));
         }

         $result = array_merge(...$result);

        return $result;
     }

    //utility functions
    private function validateData($data, $scenario) {
        $itemValidator = new ItemValidator($data, $scenario);
        return $itemValidator->validate();
    }
}