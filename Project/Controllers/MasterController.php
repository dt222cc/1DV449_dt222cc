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
            var_dump("Yes, the user did a submission");

            if ($this->view->validateFields()) {
                var_dump(" and it passed.");

                // ...get location coordinates

                // ...get train times with coordinates and data from form (using the view)

                // ...get forecasts from the coordinates and datetime from another form as same but (simplified, by period, ex: 12:00 to 18:00)
                // The important part is getting the different weather from two locations and less focus on the precision :D
            } else {
                var_dump(" and it didn't pass.");
            }

            // Also need to handle exceptions to view (webservice down, etc)
        }
    }
}