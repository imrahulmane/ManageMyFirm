<?php


namespace App\util;
use MongoDB;


class DatabaseUtil
{
    private $dbName="managefirm";
    public function getConnection($collection_name) {
        $client = new MongoDB\Client('mongodb://172.18.0.11:27017');
        $db = $client->selectDatabase($this->dbName);
        $options = ["typeMap" => ['root' => 'array', 'document' => 'array']];
        $colln = $db->selectCollection($collection_name,$options);
        return $colln;
    }
}