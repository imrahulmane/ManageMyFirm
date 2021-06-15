<?php


namespace App\providers;


use App\util\BaseDataProvider;

class CustomerDataProvider extends BaseDataProvider
{

    protected function collection()
    {
        // TODO: Implement collection() method.
        return 'customer';
    }
}