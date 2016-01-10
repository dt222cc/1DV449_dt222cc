<?php

/**
 * Openweathermap
 */
class ForecastAPI {
    /**
     * Get forecast from API with coordinates
     *
     * @param object
     * @return object[] | null
     */
    public function getForecast($location)
    {
        $lat = $location->lat;
        $lng = $location->lng;

        // Establish connection, get xml
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lng&units=metric&appid=".Settings::WEATHERKEY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        // Throw exception if connection failed on no search results
        if ( $data === "" || $data === null /* Perhaps something else, when I get to it */) {
            // throw new exception(); // Reminder: Add custom execeptions
            return null;
        }

        // $json = json_decode(file_get_contents("forecasts.json"));
        $json = json_decode($data);

        // Prep today, format YYYY-MM-DD
        $today = getdate();
        $todayStr =  $today['year']."-".sprintf("%02d", $today['mon'])."-".sprintf("%02d", $today['mday']);

        $forecasts = array();

        // The logic here should be okay, the first forecasts is the current day, which is what i keep
        foreach ($json->list as $forecast) {
            // Only retrieve todays forecasts
            if (explode(' ', $forecast->dt_txt)[0] === $todayStr) {
                $object = new stdClass();
                $object->location = $location->toponymName;
                $object->forecastTime = $forecast->dt_txt;
                $object->temperature = $forecast->main->temp;
                $object->icon = $forecast->weather[0]->icon;
                $object->description = $forecast->weather[0]->description;
                $forecasts[] = $object;
            } else {
                // break, return forecasts when there is no more forecasts for the day
                return $forecasts;
            }
        }
        // If this are being reached then an error occured
        return null;
    }
}