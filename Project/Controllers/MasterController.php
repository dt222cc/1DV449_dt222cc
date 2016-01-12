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
            // ...get location from cache
            var_dump($_SESSION['locations']);
            var_dump($_SESSION['forecasts']);

            // ...no match from the cache, get from the database/webservice
            $re = $this->model->getLocations($this->view->getOrigin(), $this->view->getDestination());
            if ($re === true) {
                echo 'Locations pass! ';
                // ...get forecasts (same as above, locations)
                $re = $this->model->getForecasts($this->view->getDateTime());
                if ($re === false) {
                    // ...something for error presentation
                    echo ' Forecasts fail! ';
                }
                else {
                    echo 'Forecasts pass! ';
                }
            }
            else {
                echo 'Locations fail! ';
            }

            // Need to upgrade exception handling
        }
    }
}