<?php

namespace Hestec\OpenWeatherMap;

use SilverStripe\ORM\DataExtension;
use Cmfcmf\OpenWeatherMap;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class OpenWeatherMapExtension extends DataExtension {

    public function WeatherForecast($days = 6, $city){

        $today = new \DateTime();

        // maximum forecast days with the free account is today + 5, so 6
        if ($days > 6){
            $days = 6;
        }

        if ($data = OpenWeatherMapData::get()->filter(array('CityId' => $city, 'Date' => $today->format('Y-m-d')))->first()){

            $now = new \DateTime();
            $now->modify("- 1 hour");

            if ($data->Created < $now->format('Y-m-d H:i')){

                $this->UpdateOwm($city);

            }

        }else{

            $this->UpdateOwm($city);

        }

        $count = 0;
        $output = new ArrayList();

        while ($count < $days){

            $date = $today->format('Y-m-d');

            $weather = $this->getWeatherData($date, $city);

            $output->push(
                new ArrayData(array(
                    'CityName' => $weather->CityName,
                    'CityCountry' => $weather->CityCountry,
                    'Date' => $date,
                    'TemperatureMin' => round($weather->TemperatureMin),
                    'TemperatureMax' => round($weather->Temperature),
                    'Icon' => $weather->Icon,
                    'IconUrl' => $weather->IconUrl
                ))
            );

            /*$output[]['Date'] = $date;
            $output[]['TemparatureMin'] = $this->getTemperatureMin($date);*/
            $today->modify("+ 1 day");

            $count++;

        }

        //print_r($output);

        return $output;

    }

    public function UpdateOwm($city){

        $list = OpenWeatherMapData::get();

        foreach($list as $item) {
            $item->delete();
        }

        $owm = new OpenWeatherMap('6a57afde213d03c6209c3c2c38beac75');

        $forecast = $owm->getWeatherForecast($city, 'metric', 'nl', '', 5);

        foreach ($forecast as $weather) {

            $add = new OpenWeatherMapData();
            $add->CityId = $forecast->city->id;
            $add->CityName = $forecast->city->name;
            $add->CityCountry = $forecast->city->country;
            $add->Date = $weather->time->day->format('Y-m-d');
            $add->Temperature = $weather->temperature->getValue();
            $add->Icon = $weather->weather->icon;
            $add->IconUrl = $weather->weather->getIconUrl();
            $add->write();

        }

    }

    public function getWeatherData($date, $city){

        $output = OpenWeatherMapData::get()->filter(array('Date' => $date, 'CityId' => $city))->sort('Temperature')->last();

        $min = OpenWeatherMapData::get()->filter(array('Date' => $date, 'CityId' => $city))->sort('Temperature')->first();

        $output = $output->customise([
            'TemperatureMin' => $min->Temperature
        ]);

        return $output;

    }

}