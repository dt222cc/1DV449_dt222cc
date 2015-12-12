<?php

class HTMLView
{
    private static $trafficTitle = 'title';
    private static $trafficDate = 'createddate';
    private static $trafficLocation = 'exactlocation';
    private static $trafficDescription = 'description';
    private static $trafficCategory = 'subcategory';

    public function __construct() {
        date_default_timezone_set('Europe/Stockholm');
    }

    /**
     * Render the HTML
     *
     * @param array[] Traffic list
     */
    public function render($traffic) {
        echo "
<!DOCTYPE html>
<html lang='sv'>
    <head>
        <meta charset='UTF-8'>
        <title>dt222cc - Laboration 3</title>
        <link rel='stylesheet' href='css/design.css' />
        <link rel='stylesheet' href='lib/leaflet.css' />
    </head>

    <body>
        <div id='container'>
            <div id='map'>
            </div>
            <div>
                <button id='reset'>Återställ</button>
            </div>
            <div id='traffic-list-container'>
                Välj kategori:
                <select id='filter'>
                </select>
                <ul id='traffic-list'>" . $this->getTrafficList($traffic) . "
                </ul>
            </div>
        </div>
        <script src='lib/leaflet.js'></script>
        <script src='lib/jquery-1.11.3.min.js'></script>
        <script src='TrafficApp.js'></script>
    </body>
</html>";
    }

    /**
     * Builds the traffic list with the traffic data
     *
     * @param  array[]
     * @return string HTML
     */
    private function getTrafficList($traffic) {
        $string = "";

        // For each traffic incident add a list-tag with title, date, location and description
        foreach ($traffic as $t) {
            // Normal strings
            $title = $t[self::$trafficTitle];

            // Have to format date
            $date = $this->getDateHTML($t[self::$trafficDate]);

            // These fields can be empty which messes up linebreaks
            $location = $this->getLocationHTML($t[self::$trafficLocation]);
            $description = $this->getDescriptionHTML($t[self::$trafficDescription]);
            $category = $this->getCategoryHTML($t[self::$trafficCategory]);

            $string .= "
                    <li>
                        <a class='incident' href='#'>$title</a>
                        <p class='incident-details'>
                            $category
                            $date
                            $location
                            $description
                        </p>
                    </li>";
        }

        return $string;
    }

    /**
     * Helper methods for getTrafficView.
     *
     * @param   string
     * @return  string HTML | null
     */
    private function getDateHTML($date) {
        $date = substr($date, 6, 10);
        $dateTime = new DateTime("@$date");
        $dateFormat = $dateTime->format('j F Y'); // ex: 11 December 2015. English or local, dunno
        return "Datum: $dateFormat<br>";
    }
    private function getCategoryHTML($category) {
        return trim($category) != "" ? "Kategori: $category<br>" : "";
    }
    private function getLocationHTML($location) {
        return trim($location) != "" ? "Plats: $location<br>" : ""; // If/Else Using Ternary Operators
    }
    private function getDescriptionHTML($description) {
        return trim($description) != "" ? "Beskrivning: $description" : "";
    }
}