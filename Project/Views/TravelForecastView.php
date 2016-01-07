<?php

class TravelForecastView
{
    private $message = "";

    /**
     * @var /View/TravelForecastModel
     */
    private $model;

    /**
     * Dependency injection, use somekind of model to build HTML from, else cache
     *
     * @param /View/TravelForecastModel
     */
    public function __construct(TravelForecastModel $m)
    {
        $this->model = $m;
    }

    /**
     * Accessor method, form submission
     *
     * @return boolean
     */
    public function didUserSubmitForm()
    {
        return isset($_GET['s1']);
    }

    /**
     * Accessor methods, API param
     *
     * @return mixed
     */
    public function getOrigin()
    {
        if (isset($_GET['O'])) {
            $a = array("å", "ä", "ö");
            $b = array("a", "a", "o");
            $c = str_replace($a, $b, strtolower(trim($_GET['O'])));
            return $c;
        }
        return "";
    }

    public function getDestination()
    {
        if (isset($_GET['Z'])) {
            $a = array("å", "ä", "ö");
            $b = array("a", "a", "o");
            $c = str_replace($a, $b, strtolower(trim($_GET['Z'])));
            return $c;
        }
        return "";
    }
    public function getTravelDate()
    {
        // Format YYYY-MM-DD
        return $this->getTravelYear()."-".$this->getTravelMonth()."-".$this->getTravelDay();
    }
    public function getTravelTime()
    {
        // Format HH:MM, departure or arrival
        return $this->getTravelByDeparture() ?
            $this->getDestinationHour() . ":" . $this->getDestinationMinute() :
            $this->getArrivalHour() . ":" . $this->getArrivalMinute();
    }

    /**
     * Form validation, departure & arrival
     *
     * @return string HTML
     */
    public function validateFields()
    {
        $this->message = "";

        if ($this->getOrigin() == "")       { $this->message .= "Fältet för avgångsplats saknas<br>"; }
        if ($this->getDestination() == "")  { $this->message .= "Fältet för destionation saknas<br>"; }
        if ($this->validateDate() == false) { $this->message .= "Fel format för datum<br>"; }
        if ($this->validateTime() == false) { $this->message .= "Fel format för tid<br>"; }

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
                $html .= $this->getTravelListHTML();
                $html .= $this->getForecastHTML();
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
     * The first part of the app, form for locations & date submition
     *
     * @return string HTML
     */
    private function getTravelHTML()
    {
        // Options for day selection
        $dayOptions = "";
        // 31 days, default selection: current day
        for ($i = 1; $i <= 31; $i++) {
            if ($i === intval(date('j'))) {
                $dayOptions .= '<option value="'.$i.'" selected="">'.$i.'</option>';
            }
            else {
                $dayOptions .= '<option value="'.$i.'">'.$i.'</option>';
            }
        }

        // Options for month selection (note swedish month names)
        $monthOptions = "";
        $months = [ "Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December" ];
        // 12 months, default selection: current month
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
        $hours1 = ""; // Departure
        $hours2 = ""; // Arrival
        for ($i = 0; $i < 24; $i++) {
            $j = sprintf("%02d", $i);
            if ($i === intval(date('G'))) { // If current hour, make "selected"
                $hours1 .= '<option value="'.$i.'" selected="">'.$j.'</option>';
                // Arrival hour offset, 1 hour later, use 0 instead of 24
                $j = $i == 23 ? sprintf("%02d", 0) : sprintf("%02d", $i + 1);
                $hours2 .= '<option value="'.$i.'" selected="">'.$j.'</option>';
            }
            else {
                $hours1 .= '<option value="'.$i.'">'.$j.'</option>';
                $hours2 .= '<option value="'.$i.'">'.$j.'</option>';
            }
        }

        // Options for minutes selection
        $minutes1 = ""; // Departure
        $minutes2 = ""; // Arrival
        for ($i = 0; $i < 60; $i++) {
            $j = sprintf("%02d", $i);
            if ($j === date('i')) { // If same as current minute, make "selected"
                $minutes1 .= '<option value="'.$i.'" selected="">'.$j.'</option>';
                $minutes2 .= '<option value="'.$i.'" selected="">'.$j.'</option>';
            } else {
                $minutes1 .= '<option value="'.$i.'">'.$j.'</option>';
                $minutes2 .= '<option value="'.$i.'">'.$j.'</option>';
            }
        }

        return '
        <div id="location-container">
            <p style ="color:#ff0000"><b>'.$this->message.'</b></p>
            <form method="get">
                <div class="date">
                    <label for="d">Dag: </label>
                    <select id="d" name="d">'.$dayOptions.'</select>
                    <label for="m">Månad: </label>
                    <select id="m" name="m">'.$monthOptions.'</select>
                    <label for="y">År: </label>
                    <select id="y" name="y">'.$yearOptions.'</select>
                </div>
                <div class="time">
                    <input type="radio" name="by" value="departure" checked=""><strong> Avgångstid</strong>
                    <select id="dH" name="dH">'.$hours1.'</select><strong> : </strong>
                    <select id="dM" name="dM">'.$minutes1.'</select>
                </div>
                <div class="time">
                    <input type="radio" name="by" value="arrival"><strong> Ankomsttid</strong>
                    <select id="aH" name="aH">'.$hours2.'</select><strong> : </strong>
                    <select id="aM" name="aM">'.$minutes2.'</select>
                </div>
                <div>
                    <label for="O"><strong>Från:</strong></label></br>
                    <input type="text" id="O" name="O" autocomplete="off" size="20" value="'.$this->getOrigin().'">
                </div>
                <div>
                    <label for="Z"<strong>Till:</strong></label></br>
                    <input type="text" id="Z" name="Z" autocomplete="off" size="20" value="'.$this->getDestination().'">
                </div>
                <div>
                    <input type="submit" name="s1" value="Skicka">
                </div>
            </form>
        </div>';
    }

    /**
     * Second part of the app, the list of train times (Note: Split into new View?)
     *
     * @return string HTML
     */
    private function getTravelListHTML()
    {
        return '
        <div id="train-list-containter">
            <h3>Tåg tider ...</h3>
        </div>';
    }

    /**
     * Third part of the app, the forecast presentation (Note: Split into new View?)
     *
     * @return string HTML
     */
    private function getForecastHTML()
    {
        $departureHTML = "";
        $arrivalHTML = "";

        return '
        <div id="forecast-containter">
            <div class="forecast">
                <h3>Väder vid avgång i ...</h3>
            </div>
            <div class="forecast">
                <h3>Väder vid ankomst i ...</h3>
            </div>
        </div>';
    }

    /**
     * Checks if the strings contains not allowed special characters
     *
     * @return bool
     */
    private function containsSpecialCharacters()
    {
        if ($this->removeSomeSpecialCharacters($this->getOrigin()) != $this->getOrigin() ||
            $this->removeSomeSpecialCharacters($this->getDestination()) != $this->getDestination()) {
            return true;
        }
        return false;
    }

    private function validateTime($hour = "", $minute = "")
    {
        // Validate departure or arrival time
        return $this->getTravelByDeparture() ?
            // Validate for number
            is_numeric($this->getDestinationHour()) && is_numeric($this->getDestinationMinute()) :
            is_numeric($this->getArrivalHour()) && is_numeric($this->getArrivalMinute());
    }

    private function validateDate()
    {
        return checkdate($this->getMonth(), $this->getDay(), $this->getYear());
    }

    /**
     * Remove characters that are not allowed
     *
     * @param string
     * @return string
     */
    private function removeSomeSpecialCharacters($string)
    {
        return preg_replace("/[^A-Za-z0-9åäöÅÄÖ?!,.;:-_ ]/", "", strip_tags($string));
    }

    /**
     * Return true or false depending on which radio button was selected, departure or arrival
     *
     * @return boolean
     */
    private function getTravelByDeparture()
    {
        return isset($_GET['by']) ? $_GET['by'] : "" == "departure";
    }

    /**
     * Getters for the form data
     *
     * @return string
     */
    private function getDay()
    {
        return isset($_GET['d']) ? trim($_GET['d']) : "";
    }
    private function getMonth()
    {
        return isset($_GET['m']) ? trim($_GET['m']) : "";
    }
    private function getYear()
    {
        return isset($_GET['y']) ? trim($_GET['y']) : "";
    }
    private function getDestinationHour()
    {
        return isset($_GET['dH']) ? trim($_GET['dH']) : "";
    }
    private function getDestinationMinute()
    {
        return isset($_GET['dM']) ? trim($_GET['dM']) : "";
    }
    private function getArrivalHour()
    {
        return isset($_GET['aH']) ? trim($_GET['aH']) : "";
    }
    private function getArrivalMinute()
    {
        return isset($_GET['aM']) ? trim($_GET['aM']) : "";
    }
}

// Do add a reset button
// Swap out textfield values to the actual name of the location, "Kalmar" should be replaced with "Kalmar Centralstation"
// for that to work i need to retrieve that data from the model, read-only principle