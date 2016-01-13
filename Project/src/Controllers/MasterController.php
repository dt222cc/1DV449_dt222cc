<?php

class MasterController
{
    /**
     * @var /Models/TravelForecastModel
     */
    private $model;

    /**
     * @var /Views/TravelForecastView
     */
    private $view;

    /**
     * Dependency injection
     *
     * @param /Views/TravelForecastModel, /Views/TravelForecastView
     */
    public function __construct(TravelForecastModel $m, TravelForecastView $v)
    {
        // Prep session variables to pass variables between PHP and JavaScript
        if ($_SESSION["locations"] === null) {
            $_SESSION["locations"] = "";
        }
        if ($_SESSION["forecasts"] === null) {
            $_SESSION["forecasts"] = "";
        }

        $this->model = $m;
        $this->view = $v;
    }

    /**
     * Handle the flow of the service
     */
    public function doTravelForecastService()
    {
        // If form has been submitted and passed the validation
        if ($this->view->didUserSubmitForm() && $this->view->validateFields()) {
            // ...get locations and forecasts from the cache which was passed on to SESSION
            $locations =  $_SESSION['locations'];
            $forecasts = $_SESSION['forecasts'];

            // Control
            echo 'Data from Session: <br>';
            var_dump($locations);
            echo '<br>';
            var_dump($forecasts);
            echo '<br>';

            // Use cache if it exists
            if ($locations !== "" && $forecasts !== "") { // Might split this
                echo '$_SESSION have locations and forecasts. ';
                // Do try to get location and forecasts out of the cache

                // If no match do the database/webservice
                $this->getFromDbWebservice($locations, $forecasts);

                // Note: probably have to alter location/forecast structure for access (search)
            }
            // Else use database (need to refactor)
            else {
                echo '$_SESSION does not have locations and forecasts. ';
                $this->getFromDbWebservice($locations, $forecasts);
            }

            // Need to upgrade exception handling
        }
    }

    /**
     * Placeholder
     */
    private function getFromDbWebservice($cacheLocations, $cacheForecasts)
    {
        // Might have to refactor this, do this if no cache 'or' no match in cache
        // ...no match from the cache, get from the database/webservice
        $locations = $this->model->getLocations($this->view->getOrigin(), $this->view->getDestination());
        if ($locations !== null) {
            echo 'Locations pass! ';
            // ...get forecasts (same as above, locations)
            $forecasts = $this->model->getForecasts($this->view->getDateTime());
            if ($forecasts !== null) {
                echo 'Forecasts pass! ';

                // Prep for local storage refresh
                $this->view->setCacheLocations($cacheLocations, $locations);
                $this->view->setCacheForecasts($cacheForecasts, $forecasts);
            }
            else {
                // ...something for error presentation
                echo 'Forecasts fail! ';
            }
        }
        else {
            echo 'Locations fail! ';
        }
    }
}