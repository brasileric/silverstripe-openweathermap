<?php

namespace Hestec\OpenWeatherMap;

use SilverStripe\ORM\DataObject;

class OpenWeatherMapData extends DataObject {

    private static $table_name = 'OpenWeatherMapData';

    private static $db = array(
        'CityId' => 'Int',
        'CityName' => 'Varchar(100)',
        'CityCountry' => 'Varchar(2)',
        'Date' => 'Date',
        'TimeFrom' => 'Time',
        'TimeTo' => 'Time',
        'Temperature' => 'Double',
        'Icon' => 'Varchar(10)',
        'IconUrl' => 'Varchar(255)'
    );

}