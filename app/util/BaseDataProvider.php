<?php


namespace App\util;


abstract class BaseDataProvider
{
    protected $dbObj;
    protected $collectionObj;

    public function __construct() {
        $this->dbObj = new DatabaseUtil();
        $this->collectionObj = $this->dbObj->getConnection($this->collection());
    }

    abstract protected function collection();

    public function insertOne($data){
        $result = $this->collectionObj->insertOne($data);
        return $result->getInsertedId();
    }

    public function updateOne($searchArray, $updateArray) {
        $result = $this->collectionObj->updateOne($searchArray, $updateArray);
        return $result->getModifiedCount();
    }

    public function updateMany($searchArray, $updateArray){
        $result = $this->collectionObj->updateMany($searchArray, $updateArray);
        return $result->getModifiedCount();
    }

    public function find($searchArray = [], $options = []) {
        return  $this->collectionObj->find($searchArray, $options)->toArray();
    }

    public function findOne($searchArray, $projection =[]){
        return $this->collectionObj->findOne($searchArray, ['projection' => $projection]);
    }

    public function deleteOne($searchArray) {
        $result = $this->collectionObj->deleteOne($searchArray);
        return $result->getDeletedCount();
    }

    public function recordCount($searchArray) {
        $result = $this->collectionObj->countDocuments($searchArray);
        return $result;
    }

    public function replaceOne($searchArray, $updateArray){
        $result = $this->collectionObj->replaceOne($searchArray, $updateArray);
        return $result;
    }

    public function aggregate($pipeline) {
        return $this->collectionObj->aggregate($pipeline)->toArray();
    }

    public  function bulkInsert($data) {
        $query = [
            'insertOne'  => [$data]
        ];
        return $query;
    }
    public  function bulkUpdate($searchArray, $updateArray) {
        $query = [
            'updateOne'  => [$searchArray, $updateArray]
        ];
        return $query;
    }
    public  function bulkDelete($searchArray) {
        $query = [
            'deleteOne'  => [$searchArray]
        ];
        return $query;
    }
    public  function bulkWrite($operations, $ordered=false) {
        return $this->collectionObj->bulkWrite($operations, ['ordered' => $ordered]);
    }


}