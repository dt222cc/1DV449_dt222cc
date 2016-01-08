<?php

require_once("Models/DAL/LocationDAL.php");
require_once("Models/API/LocationAPI.php");
require_once("Models/DAL/ForecastDAL.php");
require_once("Models/API/ForecastAPI.php");

class TravelForecastModel
{
    private $locationDAL;
    private $LocationAPI;
    private $forecastDAL;
    private $forecastAPI;

    public function __construct()
    {
        $this->locationDAL = new LocationDAL();
        $this->locationAPI = new LocationAPI();
        $this->forecastDAL = new ForecastDAL();
        $this->forecastAPI = new ForecastAPI();
    }

    /**
     * @param string
     * @return array
     */
    public function getLocation($locationName)
    {
        // Try to get locations from the database
        echo 'Trying to get location from the database. ';
        $location = $this->locationDAL->getLocation($locationName);

        // Get from webservice if no match
        if ($location === null) {
            echo 'Not found in the database, trying the webservice. ';
            $location = $this->locationAPI->getLocation($locationName);
            // No results from the location name or webservice is down
            if ($location === null) {
                echo 'Not found in the the webservice. Try again later or try another search. ';
                return null;
            }
            // Save to database
            echo 'Saving to database. ';
            $this->locationDAL->saveLocation($location);
        }
        return $location;
    }

    /**
     * @param string
     * @return array
     */
    public function getForecast($lat, $lng, $forecastTime)
    {
        // Try to get forecast from the database
        echo '</br></br>Trying to get forecasts from the database. ';
        $forecast = $this->forecastDAL->getForecast($lat, $lng, $forecastTime);
        // Get from webservice if no match
        if ($forecast === null) {
            echo 'Not found in the database, trying the webservice. ';
            $forecasts = $this->forecastAPI->getForecast($lat, $lng);
            // If webservice is down or issue with the query
            if ($forecasts === null) {
                echo 'Webservice is down, try again later. ';
                return null;
            }
            // Save to database
            echo 'Trying to save forecasts to the database </br></br>';
            $this->forecastDAL->saveForecasts($lat, $lng, $forecasts);
        }
        return $forecast;
    }
}