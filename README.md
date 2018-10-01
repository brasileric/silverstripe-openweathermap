# SilverStripe OpenWeatherMap #

Adds a simple 1 to 5 day weather forecast to your SilverStripe website with data from the free version of OpenWeatherMap (https://openweathermap.org/).
This free version gives only a 5 day/3 hours forecast, but this module turns the collected date into a 1 to 5 day full day forecast.

### Requirements ###

SilverStripe 4<br>
cmfcmf/openweathermap-php-api

### Version ###

Using Semantic Versioning.

### Installation ###

Install via Composer:

composer require "hestec/silverstripe-openweathermap": "1.*"

### Configuration ###

Signup for a (free) account at https://openweathermap.org/ and get you API key.

Add the OpenWeatherMap API key to your yaml file:
```
Hestec\OpenWeatherMap\OpenWeatherMapExtension:
  ApiKey: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
  ```

do a dev/build and flush.

### Working ###

The forecast data of the free version of OpenWeatherMap (maximum 5 days) will be stored in the table OpenWeatherMapData and is updated just once in 3 hours when someone visits the website. This method prevents for too many api calls and will show the weather even if the OpenWeatherMap service is not available for a while.

### Usage ###

Include OpenWeatherMap in your Page:
```
<% include OpenWeatherMap %>
  ```
This gives a 5 day weather forecast of the city Rio de Janeiro. I guess you want the forecast(s) of other cities. For that copy the template \silverstripe-openweathermap\templates\Includes\OpenWeatherMap.ss to your templates\Includes folder in your theme and changes the parameters of the loop call:
```
<% loop WeatherForecast(5, 3451190) %>
  ```
  Where the number is the days of forecasts (maximum 5) en the code is the city code used on OpenWeathetMap. Get the code of your city on https://openweathermap.org/city: search the name of the city and find the code in the url.
 
Or simply put this loop in your page(s):
```
<% loop WeatherForecast(5, 3451190) %>
       <p>$CityName, $CityCountry $Date:<br>
           Minimum temperatuur: $TemperatureMin<br>
           Maximum temperatuur: $TemperatureMax<br>
           <img src="$IconUrl">
       </p>
   <% end_loop %>
   ```

If you want to use your own icons instead of the standard OpenWeatherMap icons, use just $Icon and the path to your icons. For example:
```
<% loop WeatherForecast(5, 3451190) %>
       <p>$CityName, $CityCountry $Date:<br>
           Minimum temperatuur: $TemperatureMin<br>
           Maximum temperatuur: $TemperatureMax<br>
           <img src="/themes/simple/img/weather/$Icon\.png">
       </p>
   <% end_loop %>
   ```
   Find a list of the necessary icons here: https://openweathermap.org/weather-conditions

### Issues ###

No known issues.

### Todo ###

Make an elemental extension.<br>
Tests.
