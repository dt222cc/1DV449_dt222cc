<?php

require_once("Models/LocationDAL.php");
require_once("Models/LocationAPI.php");
require_once("Models/ForecastDAL.php");
require_once("Models/ForecastAPI.php");

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
     * Initialized the data access layers and webservices
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
     * Get and set the locations
     *
     * @param string, string
     * @return boolean
     */
    public function getLocations($originName, $destinationName)
    {
        $this->originLocation = $this->getLocation($originName);
        if ($this->originLocation === null) {
            return false;
        }
        $this->destinationLocation = $this->getLocation($destinationName);
        if ($this->destinationLocation === null) {
            return false;
        }
        return true;
    }

    /**
     * Get and set the forecasts
     *
     * @param string
     * @return boolean
     */
    public function getForecasts($forecastTime)
    {
        $this->originForecast = $this->getForecast($this->getOriginLocation(), $forecastTime);
        if ($this->originForecast === null) {
            return false;
        }
        $this->destinationForecast = $this->getForecast($this->getDestinationLocation(), $forecastTime);
        if ($this->destinationForecast === null) {
            return false;
        }
        return true;
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
        echo '</br></br>';
        var_dump($location->toponymName);
        // Try to get forecast from the database
        echo '</br></br>Trying to get forecasts from the database. ';
        $forecast = $this->forecastDAL->getForecast($location, $forecastTime);
        // Get from webservice if no match
        if ($forecast === null) {
            echo 'Not found in the database, trying the webservice. ';
            $forecasts = $this->forecastAPI->getForecast($location, $forecastTime);
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