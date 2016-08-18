<?php

/**
 * There is opimization and refactoring to be done, for example less code here and more JavaScript, naming
 */
class TravelForecastView
{
    private $errorMessage = "";

    /**
     * @var /View/TravelForecastModel
     */
    private $model;

    private $cacheLocations;
    private $cacheForecasts;

    /**
     * Dependency injection, use some kind of model to build HTML from, else cache
     *
     * @param /View/TravelForecastModel
     */
    public function __construct(TravelForecastModel $model)
    {
        $this->model = $model;
    }

    /**
     * Prep the cache with new locations and forecasts
     *
     * @param $locations
     * @param $forecasts
     */
    public function setCacheData($locations, $forecasts)
    {
        $this->cacheLocations = json_encode($locations) ? json_encode($locations) : "damnåäö"; //app.js
        $this->cacheForecasts = json_encode($forecasts) ? json_encode($forecasts) : "damnåäö";
    }

    public function setErrorMessage($case)
    {
        $message = "";

        switch ($case){
            case 0:
                $message = "An unexpected error occured, try again another time or with different data.";
                break;
            case 1:
                $message = "The webservice could not find the place or forecast for the given time." +
                " Check the details again, try with another time/date or come back another time.";
                break;
        }

        $this->errorMessage .= $message;
    }
    /**
     * Accessor method, form submission
     *
     * @return boolean
     */
    public function didUserSubmitForm()
    {
        return isset($_POST['s1']);
    }

    /**
     * Trim text, convert to lower case, replace åäö to aao
     *
     * @return string
     */
    public function getOrigin()
    {
        return isset($_POST['O']) ? str_replace(array("å", "ä", "ö"), array("a", "a", "o"), strtolower(trim($_POST['O']))) : "";
    }

    /**
     * Trim text, convert to lower case, replace åäö with aao
     *
     * @return string
     */
    public function getDestination()
    {
        return isset($_POST['Z']) ? str_replace(array("å", "ä", "ö"), array("a", "a", "o"), strtolower(trim($_POST['Z']))) : "";
    }

    /**
     * Compile/format date and time so it can be used  to match with database, api forecast time
     *
     * @return string
     */
    public function getDateTime()
    {
        $date = $this->getDate();
        $hour = $this->getHours();
        $minute = $this->getMinutes();

        // Convert to int, so I can handle this logic better
        $time = intval($hour . sprintf( "%02d", $minute));

        // Depending on the time, convert to any of 00, 03, 06, 09, 12, 15, 18, 21 (coz of three hour forecasts from API)
        if ($time >= 2230)  {
            $timeStr = "00";
            $date = date('Y-m-d',strtotime($date . "+1 days"));
        }
        else if ($time >= 1930)  { $timeStr = "21"; }
        else if ($time >= 1630)  { $timeStr = "18"; }
        else if ($time >= 1330)  { $timeStr = "15"; }
        else if ($time >= 1030)  { $timeStr = "12"; }
        else if ($time >= 730)   { $timeStr = "09"; }
        else if ($time >= 430)   { $timeStr = "06"; }
        else if ($time >= 130)   { $timeStr = "03"; }
        else                     { $timeStr = "00"; }

        // Format YYYY-MM-DD HH:MM:SS
        return $date." " . $timeStr . ":00:00";
    }

    /**
     * Form validation, departure & arrival.
     * Note: Could/should perhaps handle error messages felmeddelandet in the client and work with exceptions here
     *
     * @return string HTML
     */
    public function validateFields()
    {
        $message = ""; // Temp variable, using $this->errorMessage instead of $message results in duplicated entries.

        if ($this->getOrigin() == "")                   { $message .= "Input field for the first location is missing<br>"; }
        if ($this->getDestination() == "")              { $message .= "Input field for the second location is missing<br>"; }
        if ($this->validateTime() == false)             { $message .= "Wrong format on time<br>"; }
        if ($this->containsSpecialCharacters() == true) { $message .= "Field contains some invalid characters<br>"; }

        $this->errorMessage = $message;

        return $this->errorMessage == "";
    }

    /**
     * Get HTML depending on the situation
     *
     * @return string HTML
     */
    public function getResponse()
    {
        $html = '<div id="travel-forecast-container">';

        // In progress, If user did a submission, validation and stuff
        if ($this->didUserSubmitForm()) {
            $re = $this->validateFields();

            // Fields must be validated before getting the travel html (error messages: $this->message)
            $html .=  $this->getTravelHTML();

            if ($re == true) { // If validation passed, add more parts (WIP!)
                if ($this->model->getOriginLocation() !== null &&
                    $this->model->getDestinationLocation() !== null &&
                    $this->model->getDestinationForecast() !== null &&
                    $this->model->getOriginForecast() !== null)
                {
                    $html .= $this->getForecastHTML();
                    $html .= $this->addHiddenFieldForCache();
                }
            }
        }
        else {
            // The starting view/page
            $html .=  $this->getTravelHTML();
        }

        // Close div and returns the html to be rendered
        return $html .= "</div>";
    }

    /**
     * The first part of the app, form for locations & date submission
     *
     * @return string HTML
     */
    private function getTravelHTML()
    {
        $hours = "";
        $minutes = "";
        $alertBox = "";

        // Options for hour selection
        for ($i = 0; $i < 24; $i++) {
            $twoDigit = sprintf("%02d", $i); // Add a \0 if not two characters long: 0 > 00, 1 > 01, 10 still 10
            // If current hour, make "selected"
            if ($i === intval(date('G'))) {
                $hours .= '<option value="' . $i . '" selected="">' . $twoDigit . '</option>';
            } else {
                $hours .= '<option value="' . $i . '">' . $twoDigit . '</option>';
            }
        }

        // Options for minutes selection, should be between 0 and 59
        for ($i = 0; $i < 60; $i++) {
            $twoDigit = sprintf("%02d", $i);
            // If same as current minute, make "selected"
            if ($twoDigit === date('i')) {
                $minutes .= '<option value="' . $i . '" selected="">' . $twoDigit . '</option>';
            } else {
                $minutes .= '<option value="' . $i . '">' . $twoDigit . '</option>';
            }
        }

        // Could be broken..
        if ($this->errorMessage !== "") {
            $alertBox .= '
                <div class="alert alert-danger" role="alert">
                    ' . $this->errorMessage . '
                </div>';
        }

        return '
                <div id="location-container">
                    <div class="page-header">
                        <h1 class="text-center">Weather Comparison</h1>
                        <p>* UTF8 Support not completely finished/solved.</p>
                        <p>* Because of the new of offline support, a few error messages does not work like empty input fields.</p>
                        <p>* Have not converted the error messages from swedish to english yet :D</p>
                    </div>
                    <div class="well">
                        ' . $alertBox . '
                        <form name="weatherForm" id="weatherForm" class="form-horizontal" method="post">
                            <div class="form-group">
                                <label for="theDate" class="col-sm-2 control-label">Date: </label>
                                <div class="col-sm-10">
                                    <input type="date" id="theDate" name="theDate">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="hour" class="col-sm-2 control-label">Time: </label>
                                <div class="col-sm-10">
                                    <select id="hour" name="hour">' . $hours . '</select><strong> : </strong><select id="minute" name="minute">' . $minutes . '</select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="O" class="col-sm-2 control-label">Location 1:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="O" name="O" autocomplete="off" size="20" value="' . $this->getOrigin() . '">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="Z" class="col-sm-2 control-label">Location 2:</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="Z" name="Z" autocomplete="off" size="20" value="' . $this->getDestination() . '">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <input class="btn btn-primary" type="submit" id="submit" name="s1" value="Get forecast">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="offline-div" class="alert-danger" hidden>
                    <p>You are offline! Limited functionality, only able to do stored searches.</p>
                </div>
            ';
    }

    /**
     * The forecast presentation
     *
     * @return string HTML
     */
    private function getForecastHTML()
    {
        $oL = $this->model->getOriginLocation();
        $dL = $this->model->getDestinationLocation();
        $oF = $this->model->getOriginForecast();
        $dF = $this->model->getDestinationForecast();

        return '
        <div id="forecasts-container">
            <div class="forecast">
                <h3>' . $oL->toponymName . '<small>(' . $oL->lat . ', ' . $oL->lng . ')</small></h3>
                <p>
                    ' . $this->getDateTime() . '
                </p>
                <div class="weather-symbol">
                    <img src="Content/images/' . $oF->icon . '.png" alt="weather description image" class="img-thumbnail img-responsive" width="100"/>
                </div>
                <div>' . ucfirst($oF->description) . '</div><br>
                <div class="weather-temperature">' . $oF->temperature . ' &#8451;</div>
            </div>
            <div class="forecast">
                <h3>' . $dL->toponymName . '<small>(' . $dL->lat . ', ' . $dL->lng . ')</small></h3>
                <p>
                    ' . $this->getDateTime() . '
                </p>
                <div class="weather-symbol">
                    <img src="Content/images/' . $dF->icon . '.png" alt="weather description image" class="img-thumbnail img-responsive" width="100"/>
                </div>
                <div>' . ucfirst($dF->description) . '</div><br>
                <div class="weather-temperature">' . $dF->temperature . ' &#8451;</div>
            </div>
        </div>
        ';
    }

    /**
     * @return string
     */
    private function addHiddenFieldForCache()
    {
        return '
            <div hidden id="temp-locations">' . $this->cacheLocations . '</div>
            <div hidden id="temp-forecasts">' . $this->cacheForecasts . '</div>
        ';
    }

    /**
     * Fields for time has to be numeric
     *
     * @return bool
     */
    private function validateTime()
    {
        return is_numeric($this->getHours()) && is_numeric($this->getMinutes());
    }

    /**
     * Checks if the strings contains not allowed special characters
     *
     * @return bool
     */
    private function containsSpecialCharacters()
    {
        if ($this->removeSomeSpecialCharacters($_POST['O']) != $_POST['O'] ||
            $this->removeSomeSpecialCharacters($_POST['Z']) != $_POST['Z'])
        {
            return true;
        }
        return false;
    }

    /**
     * Only keep characters that are allowed
     *
     * @param string
     * @return string
     */
    private function removeSomeSpecialCharacters($string)
    {
        return preg_replace("/[^A-Za-z0-9åäöÅÄÖ ]/", "", strip_tags($string));
    }

    /**
     * Getters for the form data
     *
     * @return string
     */
    private function getDate()    { return isset($_POST['theDate']) ? $_POST['theDate']      : ""; }
    private function getHours()   { return isset($_POST['hour'])    ? trim($_POST['hour'])   : ""; }
    private function getMinutes() { return isset($_POST['minute'])  ? trim($_POST['minute']) : ""; }
}
