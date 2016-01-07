<?php

class TravelForecastModel
{
    public function __construct()
    {

    }

    // Two times
    public function getCoordinatesByName($locationName)
    {
        // Do some if cases to ascertain where to get location from

        // Parse JSON and keep latitude and longitude
    }

    // Do once
    public function getTravelTimes($oLat, $oLng, $aLat, $aLng, $date, $time) // Work with objects instead? less hassle without, for now
    {
        // Do some if cases to ascertain where to get times from

        // Parse JSON or XML, have not yet determined which to use
    }

    // Two times
    public function getForecastByCoordinates($lat, $lng)
    {
        // Do some if cases to ascertain where to get forecast from

        // Parse XML and get forecast(temperature, weather description(symbolId))
    }

    // CACHE
    // If not fresh do Database
    // !Next assignment is cache on local then make it work on public server
    private function getLocationFromCache()
    {

    }

    private function getTravelTimesFromCache()
    {

    }

    private function getForecastFromCache()
    {

    }

    // DATABASE
    // If not fresh do Webservice
    // !debatable, time & motivation
    private function getLocationFromDb()
    {

    }

    private function getGetTravelTimesFromDb()
    {

    }

    private function getForecastFromDb()
    {

    }

    // WEBSERVICE
    // Do validate data from webservice
    private function getTravelTimesFromWebService()
    {
        // Depending on time, research and motivation, I'll use deep-linking (djuplänkning) or the API

        // http://reseplanerare.resrobot.se/bin/query.exe/sn?&SID=A=16@X=17703271@Y=59869065@O=Uppsala&Z=7400001&date=2015–05–29&time=13:00&timesel=arrive&start=1
        // https://api.resrobot.se/trip.<FORMAT>?key=<DIN NYCKEL>&Parametrar
        // https://api.resrobot.se/trip.xml?key=<DIN NYCKEL>&originId=7400001&destId=7400002

        // Save to db/cache

        // return unparsed
        return $data;
    }

    private function getLocationFromWebservice($locationName)
    {
        $data = $this->getCurlRequest("http://api.geonames.org/searchJSON?q=$locationName&maxRows=1&fcode=RSTN&username=".Settings::USERNAME);

        // Save to db/cache

        // return unparsed
        return $data;
    }

    private function getForecastFromWebservice($lat, $lng)
    {
        $data = $this->getCurlRequest("http://api.yr.no/weatherapi/locationforecast/1.9/?lat=$lat;lon=$lng");

        // Save to db/cache

        // return unparsed
        return $data;
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