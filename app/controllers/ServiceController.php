<?php


namespace App\controllers;


use App\providers\CustomerDataProvider;
use App\providers\ItemDataProvider;
use App\providers\ServiceDataProvider;
use App\util\BaseDataProvider;
use App\validators\ServiceValidator;
use DateInterval;
use DatePeriod;
use DateTime;
use MongoDB\BSON\ObjectId;

class ServiceController
{
    public function addService($data){
        //validate data
        $servicevalidator = new ServiceValidator($data, 'add');
        $servicevalidator->validate();

        //insert data into collection
        $serviceDataProvider = new ServiceDataProvider();

        $data['status'] = 'active'; //set status

        $result = $serviceDataProvider->insertOne($data);

        if(!$result) {
            //return false if data is not inserted
            return [
                'status' => 'failed',
                'message' => 'There is problem in inserting data'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Successfully added service'
        ];
    }

    public function updateService($serviceId, $data){
        //validate data
        $servicevalidator = new ServiceValidator($data, 'update');
        $servicevalidator->validate();


        //check service is completed or not
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = ['_id' => new ObjectId($serviceId)];

        $service = $serviceDataProvider->findOne($searchArray);
        $status = $service['status'];

        //check if status is completed, if yes then user can't update information
        if($status == 'completed') {
            return[
                'status' => 'failed',
                'message' => "The service you are trying to update is completed. You can't change it"
            ];
        }

        $updateArray = ['$set' => $data];
        $serviceDataProvider->updateOne($searchArray, $updateArray);

        return[
            'status' => 'success',
            'message' => 'Service updates successfully'
        ];

    }

    public function getService($customerId){
        $customerDataProvider = new CustomerDataProvider();
        $searchArray = ['_id' => new ObjectId($customerId)];
        $customer = $customerDataProvider->findOne($searchArray);
        $customerFullName = $customer['first_name'] . ' ' . $customer['middle_name'] . ' ' .$customer['last_name'];

        //getService of given customer ID
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = ['cust_id' => $customerId];
        $services = $serviceDataProvider->find($searchArray);

        foreach ($services as $key => $service) {
            $services[$key]['customer_name'] = $customerFullName;
        }

        return $services;

    }

    public function getAllServices($searchCriteria){
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = [];

        if(!empty($searchCriteria)){
            $searchArray = $searchCriteria;
        }

        $services = $serviceDataProvider->find($searchArray);

        //get customer name
        $customerIds = [];
        foreach ($services as $service) {
            $customerIds [] = new ObjectId($service['cust_id']);
        }

        $customerIdAndNameMapping = $this->getCustomerIdAndNameMapping($customerIds);

        foreach ($services as $key => $service) {
            $services[$key]['customer_name'] = $customerIdAndNameMapping[$service['cust_id']];
        }
        return $services;
    }


    public function deleteService($serviceId){
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = ['_id' => new ObjectId($serviceId)];
        $isDeleted = $serviceDataProvider->deleteOne($searchArray);

        if($isDeleted == 0) {
            return [
                'status' => 'failed',
                'message' => 'Service ID is invalid'
            ];
        }

        return[
            'status' => 'success',
            'message' => 'Service is deleted successfully!'
        ];

    }

    public function completeService($serviceId){
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = ['$and' => [['_id' => new ObjectId($serviceId)], ['status' => 'active']]];
        $foundService = $serviceDataProvider->findOne($searchArray);

        if($foundService == false) {
            return [
                'status' => 'failed',
                'message' => 'Invalid service ID or Service is already completed'
            ];
        }
        $foundService['total_service_price'] = 0; //Initially, set total_service_price to zero

        foreach ($foundService['services'] as $key => $service) {
            //get service hours
            $startDateTime = $service['start_date_time'];
            $endDateTime = $service['end_date_time'];
            $serviceHours = $this->getServiceHours($startDateTime, $endDateTime);
            //calculate total price
            $totalPrice = $this->calculateTotalPrice($serviceHours, $service['item_id'], $service['quantity']);
            $foundService['services'][$key]['service_price'] = $totalPrice;
            $foundService['total_service_price'] += $totalPrice;
        }

        $foundService['status'] = 'completed';
        //update service in collection
        $updateArray = ['$set' => $foundService];
        $serviceDataProvider->updateOne($searchArray, $updateArray);

        return [
            'status' => 'success',
            'message' => "service is completed, Please collect Rs. ".  $foundService['total_service_price']
        ];

    }

    public function stats() {
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = [
            ['$match' => ['status' => 'completed']],
            ['$unwind' => '$services'],
            ['$group' =>
                ['_id' => ['item_id' => '$services.item_id', 'custo_id' => '$cust_id'],
                  'price' => ['$sum' => '$services.service_price'],
                  'count' => ['$sum' => 1]
                ]
            ],
            [
                '$project' => [
                    '_id' => 0,
                  'customer_id' => '$_id.custo_id',
                  'item_id' => '$_id.item_id',
                  'price' => '$price',
                  'count' => '$count'
                ]
            ]
        ];

        $retrivedData = $serviceDataProvider->aggregate($searchArray);

        $customerIds = [];
        $itemIds = [];
        foreach ($retrivedData as $result) {
            if(!in_array($result['customer_id'], $customerIds)) {
                $customerIds [] = new ObjectId($result['customer_id']);
            }
            if(!in_array($result['item_id'], $itemIds)) {
                $itemIds [] = new ObjectId($result['item_id']);
            }
        }

        $customerIdAndNameMapping = $this->getCustomerIdAndNameMapping($customerIds);

        $itemSearchArray = ['_id' => ['$in' => $itemIds]];
        $itemOptions = ['projection' => ['type' => 1]];

        $itemDataProvider = new ItemDataProvider();
        $items = $itemDataProvider->find($itemSearchArray, $itemOptions);

        $itemIdAndNameMapping = [];
        foreach ($items as $item){
            $itemIdAndNameMapping[(string) $item['_id']] = $item['type'];
        }

        foreach ($retrivedData as $key => $result) {
            $retrivedData[$key]['customer_name'] = $customerIdAndNameMapping[$result['customer_id']];
            $retrivedData[$key]['item_name'] = $itemIdAndNameMapping[$result['item_id']];

            unset($retrivedData[$key]['customer_id']);
            unset($retrivedData[$key]['item_id']);
        }

        return $retrivedData;
    }

    private function getCustomerIdAndNameMapping($customerIds)
    {
        $customerDataProvider = new CustomerDataProvider();
        $options = ['projection' => ['first_name' => 1, 'middle_name' => 1, 'last_name' => 1]];
        $customerSearchArray = ['_id' => ['$in' => $customerIds]];
        $customers = $customerDataProvider->find($customerSearchArray, $options);

        $customerIdAndNameMapping = [];
        foreach ($customers as $customer) {
            $customerIdAndNameMapping[(string)$customer['_id']] = $customer['first_name'] . ' ' . $customer['middle_name'] . ' ' . $customer['last_name'];
        }
        return $customerIdAndNameMapping;
    }


    private function getServiceHours($start, $end){
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);
        $periodInterval = new DateInterval( "PT30M" );

        $period = new DatePeriod( $startDate, $periodInterval, $endDate );
        $halfHoursCount = 0;

        foreach($period as $date){
            $startofday = clone $date;
            $startofday->setTime(9, 00);

            $endofday = clone $date;
            $endofday->setTime(17, 00);

            if($date >= $startofday && $date < $endofday){
                $halfHoursCount++;
            }
        }
        return $halfHoursCount;

    }

    private function calculateTotalPrice($serviceHours, $itemId, $quantity) {
        $hours = $serviceHours / 2; // convert half hours to hour

        //get per_hour_price from item
        $itemDataProvider = new ItemDataProvider();
        $searchArray = ['_id' => new ObjectId($itemId)];
        $item = $itemDataProvider->findOne($searchArray);

        $cost_per_hour_into_quantity = $item['cost_per_hr'] * $quantity;
        $totalPrice = $cost_per_hour_into_quantity * $hours;
        return $totalPrice;
    }
}