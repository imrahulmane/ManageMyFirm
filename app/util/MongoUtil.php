<?php


namespace App\util;


use MongoDB\BSON\ObjectId;

class MongoUtil
{
    public static function convertStringIdToObjectId($ids){
        if(!is_array($ids)){
            return new ObjectId($ids);
        }

        foreach ($ids as $k => $id){
            $ids[$k] = new ObjectId($id);
        }

        return $ids;
    }

    public static function convertObjectIdToStringId($ids){
        if(!is_array($ids)){
            return (string) $ids;
        }

        foreach ($ids as $k => $id){
            $ids[$k] = (string) $id;
        }

        return $ids;
    }

}