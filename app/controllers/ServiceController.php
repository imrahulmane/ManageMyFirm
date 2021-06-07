<?php


namespace App\controllers;


use App\providers\ItemDataProvider;
use App\providers\ServiceDataProvider;
use App\validators\ServiceValidator;
use DateInterval;
use DatePeriod;
use DateTime;
use MongoDB\BSON\ObjectId;

class ServiceController
{
    public function addService($data){
        //validate data
        $this->validateData($data, 'add');

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
        $this->validateData($data, 'update');

        //check service is completed or not
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = ['_id' => new ObjectId($serviceId)];

        $service = $serviceDataProvider->findOne($searchArray);
        $status = $service['status'];

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

    public function getService($serviceId){}
    public function getAllServices(){}
    public function deleteService($serviceId){}

    public function changeStatus($serviceId){
        $serviceDataProvider = new ServiceDataProvider();
        $searchArray = ['_id' => new ObjectId($serviceId)];
        $service = $serviceDataProvider->findOne($searchArray);

        //get service hours
        $startDateTime = $service['start_date_time'];
        $endDateTime = $service['end_date_time'];
        $serviceHours = $this->getServiceHours($startDateTime, $endDateTime);

        //calculate total price
        $totalPrice = $this->calculateTotalPrice($serviceHours, $service['action_id']);
    }

    private function validateData($data, $scenario){
        $servicevalidator = new ServiceValidator($data, $scenario);
        return $servicevalidator->validate();
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

            if($date > $startofday && $date <= $endofday){
                $halfHoursCount++;
            }
        }
        return $halfHoursCount;
    }

    private function calculateTotalPrice($serviceHours, $itemId) {
        $hours = $serviceHours / 2; // convert half hours to hour

        //get per_hour_price from item
        $itemDataProvider = new ItemDataProvider();
        $searchArray = ['_id' => new ObjectId($itemId)];
        $item = $itemDataProvider->findOne($searchArray);

        $cost_per_hour = $item['cost_per_hr'];
        $totalPrice = $cost_per_hour * $hours;
        return $totalPrice;
    }
}