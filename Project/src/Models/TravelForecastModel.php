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
     * Setters for the controller
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
        $location = $this->locationDAL->getLocation($locationName);

        // Get from webservice if no match
        if ($location === null) {
            $location = $this->locationAPI->getLocation($locationName);
            // No results from the location name or webservice is down
            if ($location === null) {
                throw new NoResultsException();
            }
            // Save to database
            if ($this->locationDAL->saveLocation($location) === false) {
                //echo 'Failed to save. App still works but the results was not saved';
            }
            //echo 'Successfully saved location.';
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
        $forecast = $this->forecastDAL->getForecast($location, $forecastTime);
        // Get from webservice if no match
        if ($forecast === null) {
            $forecasts = $this->forecastAPI->getForecasts($location, $forecastTime);
            // If webservice is down or issue with the query
            if ($forecasts === null || count($forecasts) === 0) {
                throw new NoResultsException();
            }
            // Save to database
            if ($this->forecastDAL->saveForecasts($location, $forecasts) === true) {
                //echo 'Successfully saved forecasts. ';
                foreach ($forecasts as $f) {
                    if ($f->forecastTime === $forecastTime) {
                        //echo 'Retrieved the forecast from the list. ';
                        $forecast = $f;
                    }
                }
            }
        }
        return $forecast;
    }
}