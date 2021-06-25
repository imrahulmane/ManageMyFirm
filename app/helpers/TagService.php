<?php


namespace App\helpers;


use App\providers\TagDataProvider;
use App\util\BaseDataProvider;
use App\util\MongoUtil;
use MongoDB\BSON\ObjectId;

class TagService
{
    public function addTags($dataProvider, $itemID, $name, $module, $tags = []) {

        //add system tag to the customer
        $systemTagIds = $this->addSystemTag($itemID, $name, $module);

        if(!empty($tags)){
            $tagIds = $this->addCustomTags($itemID, $tags, $module);
        }

        $searchArray = ['_id' => new ObjectId($itemID)];
        $item = $dataProvider->findOne($searchArray);

        if(!empty($tags)){
            $item['system_tags'] = $systemTagIds;
            $item['tags'] = $tagIds;
        } else {
            $item['system_tags'] = $systemTagIds;
        }

        $updateArray = ['$set' => $item];
        $dataProvider->updateOne($searchArray, $updateArray);
    }

    private function addSystemTag($customerId, $customerName, $module) {
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

    private function addCustomTags($customerId, $tags, $module){
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

    public static function getTagNames($system_tags){
        $tagIds = MongoUtil::convertStringIdToObjectId($system_tags);

        $tagDataProvider = new TagDataProvider();

        $searchArray = ['_id' => ['$in' => $tagIds]];
        $options = ['projection' => ['_id' => 0, 'tag_name' => 1]];
        $tagData = $tagDataProvider->find($searchArray, $options);

        return array_column($tagData, 'tag_name');
    }

}