<?php


namespace App\providers;


use App\util\BaseDataProvider;

class HistoryDataProvider extends BaseDataProvider
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'search_history';
    }
}