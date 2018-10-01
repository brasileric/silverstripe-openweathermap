<% loop WeatherForecast(5, 3451190) %>

    <p>$CityName, $CityCountry $Date:<br>
        Minimum temperatuur: $TemperatureMin<br>
        Maximum temperatuur: $TemperatureMax<br>
        <img src="$IconUrl">
    </p>

<% end_loop %>
