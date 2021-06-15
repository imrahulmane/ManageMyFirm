<?php


namespace App\providers;
use App\util\BaseDataProvider;

class CounterDataProvider extends BaseDataProvider
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'Counters';
    }
}