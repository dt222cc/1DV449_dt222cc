<?php

/**
 * API: Geonames
 */
class LocationAPI {
    /**
     * @param string, string
     * @return array
     */
    public function getLocation($locationName)
    {
        // Establish connection, get json
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.geonames.org/searchJSON?q=$locationName&maxRows=1&username=".Settings::GEOUSER); // &fcode=RSTN
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        //Throw exception if connection failed on no search results
        if ( $data === "" || $data === null || json_decode($data)->totalResultsCount === 0) {
            // throw new exception(); // Reminder: Add custom execeptions
            return null;
        }
        // Parse json to associative array and simplify
        $location = json_decode($data);
        return (object) [
            'toponymName' => $location->geonames[0]->toponymName,
            'name' => $locationName,
            'lat' => $location->geonames[0]->lat,
            'lng' => $location->geonames[0]->lng
        ];
    }
}