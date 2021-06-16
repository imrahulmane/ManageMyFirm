<?php


namespace App\controllers;


use App\providers\HistoryDataProvider;

class HistoryController
{
    public function getHistory($searchCriteria) {
        $historyDataProvider = new HistoryDataProvider();
        $searchArray = $searchCriteria;
        $options = ['projection' => ['_id' => 0, 'search' => 1], 'limit' => 5];
        $histories = $historyDataProvider->find($searchArray, $options);

        $result = array_column($histories, 'search');
        return $result;
    }

}