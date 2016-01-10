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
            // ...get locations
            $re = $this->model->getLocations($this->view->getOrigin(), $this->view->getDestination());
            if ($re === true) {
                echo 'Locations pass! ';
                // ...get forecasts
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