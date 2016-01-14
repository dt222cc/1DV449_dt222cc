<?php

class MasterController
{
    /**
     * @var TravelForecastModel
     * @var TravelForecastView
     */
    private $model;
    private $view;

    /**
     * MasterController constructor.
     * @param TravelForecastModel $model
     * @param TravelForecastView $view
     */
    public function __construct(TravelForecastModel $model, TravelForecastView $view)
    {
        // Prep session variables to pass variables between PHP and JavaScript
        if ($_SESSION["locations"] === null) {
            $_SESSION["locations"] = "";
        }
        if ($_SESSION["forecasts"] === null) {
            $_SESSION["forecasts"] = "";
        }

        $this->model = $model;
        $this->view = $view;
    }

    /**
     * Handle the flow of the service, decide between cache and database/webservice
     */
    public function doTravelForecastService()
    {
        // If form has been submitted and passed the validation
        if ($this->view->didUserSubmitForm() && $this->view->validateFields()) {
            // ...get locations and forecasts from the cache which was passed on to SESSION
            $cacheLocations = json_decode($_SESSION['locations']);
            $cacheForecasts = json_decode($_SESSION['forecasts']);

            $oLocation = $dLocation = $oForecast = $dForecast = null;

            try {
                // Get location names
                $dLocationName = $this->view->getDestination();
                $oLocationName = $this->view->getOrigin();
                // If locations from cache is available
                if ($cacheLocations !== "" && $cacheLocations !== null) {
                    // 1.1 Cache
                    $oLocation = $this->getLocationFromCache($oLocationName, $cacheLocations);
                    if ($oLocation === null) {
                        // 1.2 Database/Webservice
                        $oLocation = $this->model->getLocation($oLocationName);
                    }
                    // 2.1 Cache
                    $dLocation = $this->getLocationFromCache($dLocationName, $cacheLocations);
                    if ($dLocation === null) {
                        // 2.2 Database/Webservice
                        $dLocation = $this->model->getLocation($dLocationName);
                    }
                } else {
                    $oLocation = $this->model->getLocation($oLocationName);
                    $dLocation = $this->model->getLocation($dLocationName);
                }

                // Get datetime
                $forecastTime = $this->view->getDateTime();
                // If forecasts from cache is available (similar format as above)
                if ($cacheForecasts !== "" && $cacheForecasts !== null) {
                    // 1. Get forecast for origin
                    $oForecast = $this->getForecastFromCache($oLocation, $forecastTime, $cacheForecasts);
                    if ($oForecast === null) {
                        $oForecast = $this->model->getForecast($oLocation, $forecastTime);
                    }
                    // 2. Get forecast for destination
                    $dForecast = $this->getForecastFromCache($dLocation, $forecastTime, $cacheForecasts);
                    if ($dForecast === null) {
                        $dForecast = $this->model->getForecast($dLocation, $forecastTime);
                    }
                } else {
                    $oForecast = $this->model->getForecast($oLocation, $forecastTime);
                    $dForecast = $this->model->getForecast($dLocation, $forecastTime);
                }

                // Do another check in case we missed something
                if ($oLocation === null || $dLocation === null || $oForecast === null || $dForecast === null) {
                    throw new NoResultsException();
                }

                // So the php view can populate the forecasts
                $this->model->setOriginLocation($oLocation);
                $this->model->setDestinationLocation($dLocation);
                $this->model->setOriginForecast($oForecast);
                $this->model->setDestinationForecast($dForecast);

                // To save cache, a part of the solution (render to view for javascript to pick up and delete element)
                $this->saveCache($oLocation, $dLocation, $oForecast, $dForecast, $cacheLocations, $cacheForecasts);
            }
            catch (NoResultsException $e) {
                $this->view->setErrorMessage(1);
            }
            catch (Exception $e) {
                $this->view->setErrorMessage(0);
            }
        }
    }

    /**
     * @param $targetLocation string
     * @param $locations object[]
     * @return object | null
     */
    private function getLocationFromCache($targetLocation, $locations)
    {
        foreach($locations as $location){
            if ($location->name === $targetLocation) {
                return $location;
            }
        }
        return null;
    }

    /**
     * @param $location object
     * @param $forecastTime string
     * @param $forecasts object[]
     * @return object | null
     */
    private function getForecastFromCache($location, $forecastTime, $forecasts)
    {
        foreach($forecasts as $forecast) {
            if($forecast->forecastTime === $forecastTime &&
                $forecast->locationName === $location->name)
            {
                return $forecast;
            }
        }
        return null;
    }

    /**
     * Insert new entries to the cache
     *
     * @param $oLocation object
     * @param $dLocation object
     * @param $oForecast object
     * @param $dForecast object
     * @param $cacheLocations object[]
     * @param $cacheForecasts object[]
     */
    private function saveCache($oLocation, $dLocation, $oForecast, $dForecast, $cacheLocations, $cacheForecasts)
    {
        // Add by default, changes to false if already exists
        $addOL =  $addDL =  $addOF =  $addDF = true;

        if ($cacheLocations !== null && $cacheLocations !== "") {
            foreach($cacheLocations as $location) {
                if ($location->name === $oLocation->name) { $addOL = false; }
                if ($location->name === $dLocation->name) { $addDL = false; }
            }
        } else {
            $cacheLocations = array();
        }

        if ($cacheForecasts !== null && $cacheForecasts !== "") {
            foreach($cacheForecasts as $forecast) {
                if ($forecast->locationName === $oForecast->locationName &&
                    $forecast->forecastTime === $oForecast->forecastTime)
                {
                    $addOF = false;
                }
                if ($forecast->locationName === $dForecast->locationName &&
                    $forecast->forecastTime === $dForecast->forecastTime)
                {
                    $addDF = false;
                }
            }
        } else {
            $cacheForecasts = array();
        }

        if ($addOL) { $cacheLocations[] = $oLocation; }
        if ($addDL) { $cacheLocations[] = $dLocation; }
        if ($addOF) { $cacheForecasts[] = $oForecast; }
        if ($addDF) { $cacheForecasts[] = $dForecast; }

        $this->view->prepareCache($cacheLocations, $cacheForecasts);

        // Copy paste for the win.. and some stuff to make it more compact
    }
}