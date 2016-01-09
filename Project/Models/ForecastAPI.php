<?php

/**
 * Openweathermap
 */
class ForecastAPI {
    /**
     * Get forecast from API with coordinates
     *
     * @param object
     * @return object[]
     */
    public function getForecast($location)
    {
        // // Establish connection, get xml
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.openweathermap.org/data/2.5/forecast?lat=$location->lat&lon=$location->lng&units=metric&appid=".Settings::WEATHERKEY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        //Throw exception if connection failed on no search results
        if ( $data === "" || $data === null /* Perhaps something else, when I get to it */) {
            // throw new exception(); // Reminder: Add custom execeptions
            return null;
        }

        // $json = json_decode(file_get_contents("forecasts.json"));
        $json = json_decode($data);

        $forecasts = array();

        // Might increase the size
        for ($i = 0; $i < 24; $i++) {
            $object = new stdClass();
            $object->location = $json->city->name;
            $object->datetime = $json->list[$i]->dt_txt;
            $object->temperature = $json->list[$i]->main->temp;
            $object->icon = $json->list[$i]->weather[0]->icon;
            $object->description = $json->list[$i]->weather[0]->description;
            $forecasts[] = $object;
        }
        return $forecasts;
    }
}