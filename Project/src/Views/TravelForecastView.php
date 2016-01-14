<?php

class TravelForecastView
{
    private $message = "";
    private $serviceErrorMessage = "";

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
        $this->cacheLocations = json_encode($locations) ? json_encode($locations) : "damnåäö";
        $this->cacheForecasts = json_encode($forecasts) ? json_encode($forecasts) : "damnåäö";
    }

    public function setErrorMessage($case)
    {
        $message = "";

        switch ($case){
            case 0:
                $message = "Ett oväntat fel uppstod, försök igen senare eller ett nytt försök.";
                break;
            case 1:
                $message = "Webbtjänsten kunde inte hitta plats eller prognos, kontrollera uppgifterna igen eller försök igen senare (webbtjänst kan vara nere).";
                break;
        }

        $this->serviceErrorMessage = $message;
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
        // Get departure or arrival time
        $hour = $this->getHours();
        $minute = $this->getMinutes();

        // Convert to int, so I can handle this logic better
        $time = intval($hour . sprintf( "%02d", $minute));
        $day = intval($this->getDay());
        // Depending on the time, convert to any of 00, 03, 06, 09, 12, 15, 18, 21 (coz of three hour forecasts from API)
        if      ($time >= 2230)  { $timeStr = "00"; $day += 1; } // Can be issues when transitioning from month to month
        else if ($time >= 1930)  { $timeStr = "21"; }
        else if ($time >= 1630)  { $timeStr = "18"; }
        else if ($time >= 1330)  { $timeStr = "15"; }
        else if ($time >= 1030)  { $timeStr = "12"; }
        else if ($time >= 730)   { $timeStr = "09"; }
        else if ($time >= 430)   { $timeStr = "06"; }
        else if ($time >= 130)   { $timeStr = "03"; }
        else                     { $timeStr = "00"; }

        $today = getdate();
        // Add hours to converted time if it went passed the current time (webservice do not keep old forecasts)
        if ($today['mday'] === $day && intval($timeStr."00") < intval($today['hours'] . $today['minutes'])) {
            // Add 3 hours to get the next forecast in line
            $plus3 = intval($timeStr) + 3;
            if ($plus3 === 24) {
                $timeStr = "00";
                $day += 1;
            }
        }
        // Format YYYY-MM-DD HH:MM:SS
        return $this->getYear()."-".sprintf("%02d", $this->getMonth())."-".sprintf("%02d", $day) . " " . $timeStr . ":00:00";
    }

    /**
     * Form validation, departure & arrival
     *
     * @return string HTML
     */
    public function validateFields()
    {
        $this->message = "";

        if ($this->getOrigin() == "")                   { $this->message .= "Fältet för avgångsplats saknas<br>"; }
        if ($this->getDestination() == "")              { $this->message .= "Fältet för ankomstplats saknas<br>"; }
        if ($this->validateDate() == false)             { $this->message .= "Fel format för datum<br>"; }
        if ($this->validateTime() == false)             { $this->message .= "Fel format för tid<br>"; }
        if ($this->containsSpecialCharacters() == true) { $this->message .= "Fält innehåller ogiltiga tecken<br>"; }

        return $this->message == "";
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
                if ($this->model->getOriginLocation() !== null && $this->model->getDestinationLocation() !== null &&
                    $this->model->getDestinationForecast() !== null && $this->model->getOriginForecast() !== null)
                {
                    $html .= $this->getForecastHTML();
                    $html .= $this->addHiddenFieldForCache();
                } else {
                    // $html .= $this->getErrorMessages();
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
        // Options for day selection, 31 days, default selection: current day
        $dayOptions = "";
        for ($i = 1; $i <= 31; $i++) {
            if ($i === intval(date('j'))) {
                $dayOptions .= '<option value="'.$i.'" selected="">'.$i.'</option>';
            }
            else {
                $dayOptions .= '<option value="'.$i.'">'.$i.'</option>';
            }
        }

        // Options for month selection (note swedish month names)  12 months, default selection: current month
        $monthOptions = "";
        $months = [ "Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December" ];
        for ($i = 1; $i <= 12; $i++) {
            if ($i === intval(date('n'))) {
                $monthOptions .= '<option value="'.$i.'" selected="">'.$months[$i - 1].'</option>';
            }
            else {
                $monthOptions .= '<option value="'.$i.'">'.$months[$i - 1].'</option>';
            }
        }

        // Options for year selection (current and next year)
        $yearOptions = "";
        $currentYear = date('o');
        $nextYear = date('o',strtotime('+1 year'));
        $yearOptions = "<option value=\"$currentYear\">$currentYear</option><option value=\"$nextYear\">$nextYear</option>";

        // Options for hour selection
        $hours = "";
        for ($i = 0; $i < 24; $i++) {
            $j = sprintf("%02d", $i); // Add a \0 if not two characters long: 0 > 00, 1 > 01, 10 still 10
            // If current hour, make "selected"
            if ($i === intval(date('G'))) {
                $hours .= '<option value="'.$i.'" selected="">'.$j.'</option>';
            }
            else {
                $hours .= '<option value="'.$i.'">'.$j.'</option>';
            }
        }

        // Options for minutes selection, should be between 0 and 59
        $minutes = "";
        for ($i = 0; $i < 60; $i++) {
            $j = sprintf("%02d", $i);
            // If same as current minute, make "selected"
            if ($j === date('i')) {
                $minutes .= '<option value="'.$i.'" selected="">'.$j.'</option>';
            } else {
                $minutes .= '<option value="'.$i.'">'.$j.'</option>';
            }
        }

        return '
                <h2>Väder jämförelse</h2>
                <div id="location-container">
                    <p style ="color:#ff0000"><b>'.$this->message.$this->serviceErrorMessage.'</b></p>
                    <form method="post">
                        <div class="date">
                            <span><b>Tid: </b></span>
                            <select id="hour" name="hour">'.$hours.'</select><strong> : </strong>
                            <select id="minute" name="minute">'.$minutes.'</select>
                            <label for="d">Dag: </label>
                            <select id="d" name="d">'.$dayOptions.'</select>
                            <label for="m">Månad: </label>
                            <select id="m" name="m">'.$monthOptions.'</select>
                            <label for="y">År: </label>
                            <select id="y" name="y">'.$yearOptions.'</select>
                        </div>
                        <div>
                            <label for="O"><strong>Plats 1:</strong></label></br>
                            <input type="text" id="O" name="O" autocomplete="off" size="20" value="'.$this->getOrigin().'">
                        </div>
                        <div>
                            <label for="Z"<strong>Plats 2:</strong></label></br>
                            <input type="text" id="Z" name="Z" autocomplete="off" size="20" value="'.$this->getDestination().'">
                        </div>
                        <div>
                            <br>
                            <input type="submit" id="submit" name="s1" value="Hämta prognos">
                        </div>
                    </form>
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

        // Missing the part of time, opted to not include it because of the lack of traint-times which results in no way of establishing arrival time
        return '
        <div id="forecasts-container">
            <div class="forecast">
                <h3>Vädret i '.$oL->toponymName.' <span class="weather-coordinates">(Lat: '.$oL->lat.', Lng: '.$oL->lng.')</span></h3>
                <div>'.$this->getDateTime().'</div>
                <div>Beskrivning: '.$oF->description.'</div>
                <div class="weather-symbol">
                    <img alt="weather description image" src="/project/src/Content/images/'.$oF->icon.'.png" />
                </div>
                <div class="weather-temperature">'.$oF->temperature.' &#8451;</div>
            </div>
            <div class="forecast">
                <h3>Vädret i '.$dL->toponymName.' <span class="weather-coordinates">(Lat: '.$dL->lat.', Lng: '.$dL->lng.')</span></h3>
                <div>'.$this->getDateTime().'</div>
                <div>Beskrivning: '.$dF->description.'</div>
                <div class="weather-symbol">
                    <img alt="weather description image" src="/project/src/Content/images/'.$dF->icon.'.png" />
                </div>
                <div class="weather-temperature">'.$dF->temperature.' &#8451;</div>
            </div>
        </div>
        ';
    }

    /**
     * @return string
     */
    private function addHiddenFieldForCache()
    {
        return "
            <div hidden id=\"temp-locations\">$this->cacheLocations</div>
            <div hidden id=\"temp-forecasts\">$this->cacheForecasts</div>
        ";
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
     * Checks if the date from user input can be parsed as a "date"
     *
     * @return bool
     */
    private function validateDate()
    {
        return checkdate($this->getMonth(), $this->getDay(), $this->getYear());
    }

    /**
     * Checks if the strings contains not allowed special characters
     *
     * @return bool
     */
    private function containsSpecialCharacters()
    {
        if ($this->removeSomeSpecialCharacters($_POST['O']) != $_POST['O'] ||
            $this->removeSomeSpecialCharacters($_POST['Z']) != $_POST['Z']) {
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
    private function getDay()               { return isset($_POST['d'])  ? trim($_POST['d'])  : ""; }
    private function getMonth()             { return isset($_POST['m'])  ? trim($_POST['m'])  : ""; }
    private function getYear()              { return isset($_POST['y'])  ? trim($_POST['y'])  : ""; }
    private function getHours()             { return isset($_POST['hour']) ? trim($_POST['hour']) : ""; }
    private function getMinutes()           { return isset($_POST['minute']) ? trim($_POST['minute']) : ""; }
}