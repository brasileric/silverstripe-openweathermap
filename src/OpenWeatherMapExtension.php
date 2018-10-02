<?php

namespace Hestec\OpenWeatherMap;

use SilverStripe\ORM\DataExtension;
use Cmfcmf\OpenWeatherMap;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Core\Injector\Injector;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\Queries\SQLSelect;

class OpenWeatherMapExtension extends DataExtension {

    public function WeatherForecast($days = 5, $city){

        $today = new \DateTime();

        // maximum forecast days with the free account is today + 5, so 6
        if ($days > 5){
            $days = 5;
        }

        if ($data = OpenWeatherMapData::get()->filter(array('CityId' => $city))->first()){

            $now = new \DateTime();
            $now->modify("- 3 hours");

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

            if ($weather = $this->getWeatherData($date, $city)){
                $output->push(
                    new ArrayData(array(
                        'CityName' => $weather->CityName,
                        'CityCountry' => $weather->CityCountry,
                        'Date' => DBField::create_field('Date', $date),
                        'TemperatureMin' => round($weather->TemperatureMin),
                        'TemperatureMax' => round($weather->Temperature),
                        'Icon' => $weather->Icon,
                        'IconUrl' => $weather->IconUrl
                    ))
                );

            }else{
                Injector::inst()->get(LoggerInterface::class)->warn('OpenWeatherMap -> no records in table for date: ' . $date);
            }

            $today->modify("+ 1 day");
            $count++;

        }

        return $output;

    }

    public function UpdateOwm($city){

        $owm = new OpenWeatherMap(Config::inst()->get(__CLASS__, 'ApiKey'));

        try {

            $forecast = $owm->getWeatherForecast($city, 'metric', 'nl', '', 5);

            $today = new \DateTime();

            $list = OpenWeatherMapData::get()->exclude('Date', $today->format('Y-m-d'));

            foreach ($list as $item) {
                $item->delete();
            }

            foreach ($forecast as $weather) {

                if ($update = OpenWeatherMapData::get()->filter(array('CityId' => $forecast->city->id, 'Date' => $weather->time->day->format('Y-m-d'), 'TimeFrom' => $weather->time->from->format('H:i')))->first()) {

                    $update->Temperature = $weather->temperature->getValue();
                    $update->Icon = $weather->weather->icon;
                    $update->IconUrl = $weather->weather->getIconUrl();
                    //$update->write();

                }else{

                    $add = new OpenWeatherMapData();
                    $add->CityId = $forecast->city->id;
                    $add->CityName = $forecast->city->name;
                    $add->CityCountry = $forecast->city->country;
                    $add->Date = $weather->time->day->format('Y-m-d');
                    $add->TimeFrom = $weather->time->from->format('H:i');
                    $add->TimeTo = $weather->time->to->format('H:i');
                    $add->Temperature = $weather->temperature->getValue();
                    $add->Icon = $weather->weather->icon;
                    $add->IconUrl = $weather->weather->getIconUrl();
                    $add->write();

                }

            }

        } catch(OWMException $e) {
            Injector::inst()->get(LoggerInterface::class)->err('OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').');

        } catch(\Exception $e) {
            Injector::inst()->get(LoggerInterface::class)->err('General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').');
        }

    }

    public function getWeatherData($date, $city){

        if ($output = OpenWeatherMapData::get()->filter(array('Date' => $date, 'CityId' => $city))->sort('Temperature')->last()){

            $min = OpenWeatherMapData::get()->filter(array('Date' => $date, 'CityId' => $city))->sort('Temperature')->first();

            // get the most used icon in the day time
            // if there is not a most used it gets the last day time icon
            $query = "SELECT Icon, count(Icon) AS icons ";
            $query .= "FROM OpenWeatherMapData ";
            $query .= "WHERE CityId = '".$city."' AND Date = '".$date."' AND (SUBSTRING(Icon,3,1) = 'd') ";
            $query .= "GROUP BY Icon ";
            $query .= "ORDER BY icons DESC, TimeFrom DESC LIMIT 1";

            $icon = DB::query($query)->value();

            $output = $output->customise([
                'Icon' => $icon,
                'IconUrl' => "//openweathermap.org/img/w/".$icon.".png",
                'TemperatureMin' => $min->Temperature
            ]);

            return $output;

        }
        return false;

    }

}