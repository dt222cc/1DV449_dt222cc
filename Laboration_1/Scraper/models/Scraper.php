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
            $dom = new DOMDocument();

            if ($dom->loadHTML($data)) {
                $links = $dom->getElementsByTagName("a");

                foreach ($links as $node) {
                    $result = $node->getAttribute("href");
                    //Remove the / from the result
                    $urlExtension = preg_replace("/\//", "", $result);
                    $urls[] = $baseURL . $urlExtension . "/";
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
        $availableDays = call_user_func_array("array_intersect", $availableDays);
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
        $availableMovies = array();
        $dayOptionList = array();
        $data = $this->curlGetRequest($url);

        if ($data != null) {
            $dom = new DOMDocument();

            if ($dom->loadHTML($data)) {
                $xpath = new DOMXPath($dom);
                $dayOptions = $xpath->query("//select[@id='day']/option[not(@disabled)]");
                $movieOptions = $xpath->query("//select[@id='movie']/option[not(@disabled)]");

                //Collect available days (usage = to match days)
                //array(3) { [0]=> string(6) "Fredag" [1]=> string(7) "Lördag" [2]=> string(7) "Söndag" }
                foreach ($dayOptions as $day) {
                    $dayOptionList[] = $day->nodeValue;
                }
                /*
                 * For every available day we collect movies from that specific day.
                 * Note: Days could be handled better, used both swedish and english as I had problems with translating
                 * For example: "Lördag" === "Lördag" didn't not work, the difference being string7 and string6, odd.
                 * Could use the english days below as session variable, from the calendar site, to make it more general
                 */
                foreach($days as $availableDay) {
                    $thisDay = "";
                    if ($availableDay == "Friday") {
                        $thisDay = $dayOptionList[0];
                    }
                    if ($availableDay == "Saturday") {
                        $thisDay = $dayOptionList[1];
                    }
                    if ($availableDay == "Sunday") {
                        $thisDay = $dayOptionList[2];
                    }
                    foreach ($dayOptions as $day) {
                        //When a day match
                        if ($day->nodeValue == $thisDay) {
                            //We collect every movies for that specific day
                            foreach ($movieOptions as $movieNode) {
                                //Examples of urls: http://localhost:8080/cinema/check?day=02&movie=01 | &movie=02 | &movie=03
                                $urlForMovie = $url . "check?day=" . $day->getAttribute("value") .
                                    "&movie=" . $movieNode->getAttribute("value");
                                $data = $this->curlGetRequest($urlForMovie);
                                //$data = string(124)"[ {"status":1,"time":"16:00","movie":"02"},
                                //                      {"status":1,"time":"18:00","movie":"02"},
                                //                      {"status":0,"time":"21:00","movie":"02"}]"

                                //Format as arrays (with keys) with json_decode so we can work with it more efficient
                                $movies = json_decode($data);
                                foreach($movies as $movie) {
                                    //Keep available movies, status 1.
                                    if ($movie->status == 1) {
                                        //Add to movie list, perhaps better practise to work with model class
                                        $availableMovies[] = array(
                                            "name" => $movieNode->nodeValue,
                                            "time" => $movie->time,
                                            "day" => $availableDay,
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //Empty or with available movies
        return $availableMovies;
    }

    public function getAvailableTables($url, $movie)
    {
        $tables = array();
        $data = $this->curlGetRequest($url);

        if ($data != null) {
            $dom = new DOMDocument();

            if ($dom->loadHTML($data)) {
                $xpath = new DOMXPath($dom);
                $availableTables = $xpath->query("//input[@type='radio']");

                foreach ($availableTables as $table) {
                    $value = $table->getAttribute("value");

                    //As long as the picked movie starts/ends before the schedule
                    if($movie['day'] === "Friday" && substr($value, 0,3) === "fre") {
                        if(intval($movie['time']) < intval(substr($value, 3, 2))) {
                            $ables[] = $value;
                        }
                    }
                    if($movie['day'] === "Saturday" && substr($value, 0,3) === "lor") {
                        if(intval($movie['time']) < intval(substr($value, 3, 2))) {
                            $tables[] = $value;
                        }
                    }
                    if($movie['day'] === "Sunday" && substr($value, 0,3) === "son") {
                        if(intval($movie['time']) < intval(substr($value, 3, 2))) {
                            $tables[] = $value;
                        }
                    }
                }
            }
        }
        //Empty or with available tables
        return $tables;
    }

    /**
     * @param string
     * @return mixed Results from url (HTML)
     */
    private function curlGetRequest($url)
    {
        //Have some issues with this regarding the public server. $data returns as bool(false)
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url); //http://localhost:8080/
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
        $url = rtrim($url, "/");

        $availableDays = array();
        $data = $this->curlGetRequest($url);
        $dom = new DOMDocument();

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
}