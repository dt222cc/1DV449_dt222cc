<?php

class MasterController
{
    /**
     * @var Models/TravelForecastModel
     * @var Views/TravelForecastView
     */
    private $model;
    private $view;

    /**
     * Dependency injection
     *
     * @param Views/TravelForecastView
     */
    public function __construct(TravelForecastModel $m, TravelForecastView $v)
    {
        $this->model = $m;
        $this->view = $v;
    }

    /**
     * ...
     */
    public function doTravelForecastService()
    {
        // If form has been submitted, do validate data and...
        if ($this->view->didUserSubmitForm()) {

            if ($this->view->validateFields()) {
                    // ...get location coordinates
                    $origin = $this->model->getLocation($this->view->getOrigin());
                    // $destination = $this->model->getLocation($this->view->getDestination());

                    // ...get the forecast for specified date, time and coordinates
                    $oForecast = $this->model->getForecast($origin->lat, $origin->lng, $this->view->getDateTime());
                    // $dForecast = $this->model->getForecast($destination->lat, $destination->lng, $this->view->getDateTime());

            } else {

            }

            // Also need to handle exceptions to view (webservice down, etc)
        }
    }
}