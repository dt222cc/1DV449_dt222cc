<?php

class Scraper
{
    /**
     * @param string
     * @return string[] URL | null
     */
    public function getURLs($baseURL)
    {
        $urls = array();
        $data = $this->curlGetRequest($baseURL);

        if ($data != null) {
            $dom = new \DOMDocument();

            if ($dom->loadHTML($data)) {
                $links = $dom->getElementsByTagName('a');

                foreach ($links as $node) {
                    $result = $node->getAttribute('href');
                    //Remove the / from the result
                    $urlExtension = preg_replace('/\//', "", $result);
                    $urls[] = $baseURL . $urlExtension . '/';
                }
            }
        }
        //Empty or with URLs
        return $urls;
    }

    /**
     * @param string
     * @return string[] Available days
     */
    public function getAvailableDays($urls)
    {
        $availableDays = array();

        for($i = 0; $i < sizeof($urls); $i++) {
            $availableDays[] = $this->getCalendarOwnersAvailableDays($urls[$i]);
        }
        //Keep days that intersects
        $availableDays = call_user_func_array('array_intersect', $availableDays);
        //Rebase array keys
        $availableDays = array_values($availableDays);

        return $availableDays;
    }

    /**
     * @param string
     * @param string[] Available days
     * @return string[] Available movies
     */
    public function getAvailableMovies($url, $days)
    {
        $dayOptionsList = array();
        $availableMovies = array();
        $data = $this->curlGetRequest($url);

        if ($data != null) {
            $dom = new \DOMDocument();

            if ($dom->loadHTML($data)) {
                $xpath = new \DOMXPath($dom);
                $dayOptions = $xpath->query('//select[@id = "day"]/option[not(@disabled)]');
                $movieOptions = $xpath->query('//select[@id = "movie"]/option[not(@disabled)]');

                //Collect available days (usage = to match days)
                //array(3) { [0]=> string(6) "Fredag" [1]=> string(7) "Lördag" [2]=> string(7) "Söndag" }
                foreach ($dayOptions as $day) {
                    $dayOptionsList[] = $day->nodeValue;
                }
                //For every available day we collect movies from that specific day
                foreach($days as $availableDay) {
                    $thisDay = "";
                    if ($availableDay == "Friday") {
                        $thisDay = $dayOptionsList[0];
                    }
                    if ($availableDay == "Saturday") {
                        $thisDay = $dayOptionsList[1];
                    }
                    if ($availableDay == "Sunday") {
                        $thisDay = $dayOptionsList[2];
                    }
                    foreach ($dayOptions as $day) {
                        //When a day match
                        if ($day->nodeValue == $thisDay) {
                            //We collect every movies for the specific day
                            foreach ($movieOptions as $movieNode) {
                                //For this situation: http://localhost:8080/cinema/check?day=02&movie=01 | &movie=02 | &movie=03
                                $urlForMovie = $url . "check?day=" . $day->getAttribute("value") .
                                    "&movie=" . $movieNode->getAttribute("value");
                                $data = $this->curlGetRequest($urlForMovie);
                                //$data example:
                                //string(124)"[{"status":1,"time":"16:00","movie":"02"},{"status":1,"time":"18:00","movie":"02"},{"status":0,"time":"21:00","movie":"02"}]"

                                //Format as arrays (with keys) with json_decode so we can work with it more efficient
                                $movies = json_decode($data);

                                foreach($movies as $movie) {
                                    //Keep available movies. Should be 6 movies to keep
                                    if ($movie->status == 1) {
                                        //Add to movie list, perhaps better practise to work with model class
                                        $availableMovies[] = array(
                                            "name" => $movieNode->nodeValue,
                                            "time" => $movie->time,
                                            "day" => $day->nodeValue,
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //Empty or with movies
        return $availableMovies;
    }

    /**
     * @param string
     * @return mixed Results from url (HTML)
     */
    private function curlGetRequest($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * @param string
     * @return string[] A calendar owner's available days
     */
    private function getCalendarOwnersAvailableDays($url)
    {
        //Remove the last / because that messed things up
        $url = rtrim($url, '/');

        $availableDays = array();
        $data = $this->curlGetRequest($url);
        $dom = new \DOMDocument();

        if ($dom->loadHTML($data)) {
            $days = $dom->getElementsByTagName("th");
            $statuses = $dom->getElementsByTagName("td");

            for ($i = 0; $i < $days->length; $i++) {
                //Convert to lower case to handle inconsistency
                if (strtolower($statuses->item($i)->nodeValue) == "ok") {
                    $availableDays[] = $days->item($i)->nodeValue;
                }
            }
        }
        //Empty or with available days
        return $availableDays;
    }

    /**
     * Tried the other way, could not figure out the different string lengths (Lördag7 and Lördag6)
     * @param string[] Days in english
     * @return string[] Days in swedish
     */
    private function getDayInEnglish($dayInSwedish)
    {
        $dayInEnglish = "";

        if (strcasecmp($dayInSwedish, "Fredag") == 0) {
            $dayInEnglish =  "Friday";
        }
        if (strcasecmp($dayInSwedish, "Lördag")) {
            $dayInEnglish =  "Saturday";
        }
        if (strcasecmp($dayInSwedish, "Söndag")) {
            $dayInEnglish =  "Sunday";
        }

        return $dayInEnglish;
    }
}