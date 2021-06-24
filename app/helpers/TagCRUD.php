<?php


namespace App\helpers;


use App\providers\TagDataProvider;
use MongoDB\BSON\ObjectId;


class TagCRUD
{
    public function addSystemTag($customerId, $customerName, $module) {
        $tagIds = [];
        $tagDataProvider = new TagDataProvider();
        $tag = [
            'tag_name' => "$customerName-$module",
            'type' => 'system',
            'module' => $module,
            'reference_id' => (string) $customerId
        ];

        $tagId = $tagDataProvider->insertOne($tag);
        $tagIds [] = (string) $tagId;
        return $tagIds;
    }

    public function addCustomTags($customerId, $tags, $module){
        $tagDataProvider = new TagDataProvider();
        $tagIds = [];
        foreach ($tags as $tag) {
            $searchArray = ['tag_name' => $tag];
            $projection = ['_id' => 1];
            $isTagAvailable = $tagDataProvider->findOne($searchArray, $projection);

            if($isTagAvailable){
                $tagIds [] = (string) $isTagAvailable['_id'];
                continue;
            }

            $tagData = [
                'tag_name' => $tag,
                'type' => 'custom',
                'module' => $module,
                'reference_id' => (string) $customerId
            ];

            $tagId = $tagDataProvider->insertOne($tagData);
            $tagIds [] = (string) $tagId;
        }
        return $tagIds;
    }
}