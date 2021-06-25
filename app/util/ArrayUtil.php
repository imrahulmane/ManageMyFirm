<?php


namespace App\util;


class ArrayUtil
{
    public static function getKeyValueMap($arr, $key='_id'){
        $result = [];

        foreach ($arr as $item){
            $result[(string) $item[$key]] = $item;
        }

        return $result;
    }
}