<?php

class TravelForecastModel
{
    /**
     * Cache filename with path
     *
     * @var string
     */
    private static $locationsFilename = 'Cache/locations.json';
    private static $traintimesFilename = 'Cache/traintimes.json';
    private static $forecastsFilename = 'Cache/forecasts.json';

    /**
     * @param string
     * @return array
     */
    public function getCoordinatesByName($locationName)
    {
        $targetedLocation = null;
        // Get locations from webservice if cache doesnt exists (first time)
        if (file_exists(self::$locationsFilename) === false) {
            $targetedLocation = $this->getLocationFromWebservice($locationName);
        }
        // ...or get from the existing cache (server cache)
        else {
            var_dump("Using existing cache</br></br>");
            $locations = json_decode(file_get_contents(self::$locationsFilename));
            // ...check if location exists in cache
            foreach ($locations->locations as $location) {
                if ($location->name === $locationName) {
                    var_dump("exists");
                    $targetedLocation = $location;
                }
            }
            // ...get new location from webservice if no match
            if ($targetedLocation === null) {
                var_dump("Refresh cache / New addition</br></br>");
                $targetedLocation = $this->getLocationFromWebservice($locationName);
            }
        }

        return $targetedLocation;
    }

    /**
     * @param
     */
    public function getTravelTimes() // Work with objects instead? less hassle without, for now
    {
        // Do some if cases to ascertain where to get times from

        // Parse JSON or XML, have not yet determined which to use
    }

    /**
     * @param
     */
    public function getForecastByCoordinates($lat, $lng)
    {
        // Do some if cases to ascertain where to get forecast from

        // Parse XML and get forecast(temperature, weather description(symbolId))
    }

    /**
     * @param string, string
     * @return array
     */
    private function getLocationFromWebservice($locationName)
    {
        // Establish connection, get json/xml
        $data = $this->getCurlRequest("http://api.geonames.org/searchJSON?q=$locationName&maxRows=1&fcode=RSTN&username=".Settings::USERNAME);
        //Throw exception if connection failed on no search results
        if ( $data === "" || $data === null || json_decode($data)->totalResultsCount === 0) {
            // throw new exception(); // Reminder: Add custom execeptions
        }
        // Connection and the response passed
        else {
            // Parse json to associative array and simplify
            $data = json_decode($data);
            $location = array(
                'toponymName' => $data->geonames[0]->toponymName,
                'name' => $locationName,
                'lat' => $data->geonames[0]->lat,
                'lng' => $data->geonames[0]->lng
                );

            // Save to cache
            $this->saveToCache($location, self::$locationsFilename);
        }

        return $location;
    }

    /**
     * @param
     * @return
     */
    private function getTravelTimesFromWebService()
    {
        // Depending on time, research and motivation, I'll use deep-linking (djuplänkning) or the API

        // http://reseplanerare.resrobot.se/bin/query.exe/sn?&SID=A=16@X=17703271@Y=59869065@O=Uppsala&Z=7400001&date=2015–05–29&time=13:00&timesel=arrive&start=1
        // https://api.resrobot.se/trip.<FORMAT>?key=<DIN NYCKEL>&Parametrar
        // https://api.resrobot.se/trip.xml?key=<DIN NYCKEL>&originId=7400001&destId=7400002

        // Parse json

        $this->saveToCache($data, self::$traintimesFilename);

        return $data;
    }

    /**
     * @param string, string
     * @return
     */
    private function getForecastFromWebservice($lat, $lng)
    {
        $data = $this->getCurlRequest("http://api.yr.no/weatherapi/locationforecast/1.9/?lat=$lat;lon=$lng");

        // Parse xml

        $this->saveToCache($data, self::$forecastsFilename);

        return $data;
    }

    /**
     * Insert new entry to associated cache
     *
     * @param string, string
     */
    private function saveToCache($data, $fileName)
    {
        $this->initialCacheSetup($fileName);

        $cacheContents = json_decode(file_get_contents($fileName)); // Get existing cache contents

        if ($fileName === self::$locationsFilename) {
            array_push($cacheContents->locations, $data); // Add the new location entry to the list
        }
        else if ($fileName === self::$traintimesFilename) {
            var_dump("TRAIN TIMES");
        }
        else {
            var_dump("FORECASTS");
        }

        file_put_contents($fileName, json_encode($cacheContents));
    }

    /**
     * Create inital point of the caches if they do not exists
     *
     * @param string
     */
    private function initialCacheSetup($cacheFilename)
    {
        if (file_exists(self::$locationsFilename) === false && $cacheFilename === self::$locationsFilename) {
            $temp_array = array('locations' => array());
            file_put_contents(self::$locationsFilename, json_encode($temp_array));
        }
        if (file_exists(self::$traintimesFilename) === false && $cacheFilename === self::$traintimesFilename) {
            $temp_array = array('traintimes' => array());
            file_put_contents(self::$traintimesFilename, json_encode($temp_array));
        }
        if (file_exists(self::$forecastsFilename) === false && $cacheFilename === self::$forecastsFilename) {
            $temp_array = array('forecasts' => array());
            file_put_contents(self::$forecastsFilename, json_encode($temp_array));
        }
    }

    /**
     * Curl request, generic
     *
     * @param string Url
     * @return mixed
     */
    private function getCurlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}