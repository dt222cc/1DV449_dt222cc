<?php

require_once("LocationDAL.php");
require_once("LocationAPI.php");
require_once("ForecastDAL.php");
require_once("ForecastAPI.php");

class TravelForecastModel
{
    private $locationDAL;
    private $forecastDAL;
    private $locationAPI;
    private $forecastAPI;
    private $originLocation;
    private $destinationLocation;
    private $originForecast;
    private $destinationForecast;

    /**
     * Initialized the data access layers and webservice
     */
    public function __construct()
    {
        $this->locationDAL = new LocationDAL();
        $this->locationAPI = new LocationAPI();
        $this->forecastDAL = new ForecastDAL();
        $this->forecastAPI = new ForecastAPI();
    }

    /**
     * Getters for the view
     *
     * @return object
     */
    public function getOriginLocation()
    {
        return $this->originLocation;
    }
    public function getDestinationLocation()
    {
        return $this->destinationLocation;
    }
    public function getOriginForecast()
    {
        return $this->originForecast;
    }
    public function getDestinationForecast()
    {
        return $this->destinationForecast;
    }

    /**
     * Setters for the view
     *
     * @return object
     */
    public function setOriginLocation($location)
    {
        $this->originLocation = $location;
    }
    public function setDestinationLocation($location)
    {
        $this->destinationLocation = $location;
    }
    public function setOriginForecast($forecast)
    {
        $this->originForecast = $forecast;
    }
    public function setDestinationForecast($forecast)
    {
        $this->destinationForecast = $forecast;
    }

    /**
     * Get location from the database or from the API (save to database)
     *
     * @param string
     * @return object | null
     */
    public function getLocation($locationName)
    {
        // Try to get locations from the database
        echo '<br><br>Trying to get location from the database. ';
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
            echo 'Location was retrieved from the webservice. Saving location to the database. ';
            if ($this->locationDAL->saveLocation($location) === false) {
                echo 'Failed to save. ';
                return null;
            }
            echo 'Successfully saved location.';
        } else {
            echo 'Location was found in the database!';
        }
        return $location;
    }

    /**
     * Get forecast from the database or from the API (save to database)
     *
     * @param object, string
     * @return object | null
     */
    public function getForecast($location, $forecastTime)
    {
        // Try to get forecast from the database
        echo '</br></br>Trying to get forecasts from the database. ';
        $forecast = $this->forecastDAL->getForecast($location, $forecastTime);
        // Get from webservice if no match
        if ($forecast === null) {
            echo 'Not found in the database, trying the webservice. ';
            $forecasts = $this->forecastAPI->getForecasts($location, $forecastTime);
            // If webservice is down or issue with the query
            if ($forecasts === null) {
                echo 'Not found in the the webservice. Try again later or try another search. ';
                return null;
            }
            // Save to database
            echo 'Forecast was retrieved from the webservice. Saving forecasts to the database. ';
            if ($this->forecastDAL->saveForecasts($location, $forecasts) === false) {
                echo 'Failed to save. ';
                return null;
            }
            echo 'Successfully saved forecasts. ';
            // Try to get the specific forecast from the list
            foreach ($forecasts as $f) {
                if ($f->forecastTime === $forecastTime) {
                    echo 'Retrieved the forecast from the list. ';
                    $forecast = $f;
                }
            }
        }
        else {
            echo 'forecast was found in the database!';
        }
        return $forecast;
    }
}

// Note: echos are meant for me to see the flow better, probably gonna replace them with custom exceptions or something