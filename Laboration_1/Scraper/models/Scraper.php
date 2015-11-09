<?php

class Scraper
{
    /**
     * @param string
     * @return string[] URL | null
     */
    public function getURLs($baseURL)
    {
        $data = $this->curlGetRequest($baseURL);

        //If URL was valid and returned data
        if ($data != null) {
            $dom = new \DOMDocument();

            if ($dom->loadHTML($data)) {
                $links = $dom->getElementsByTagName('a');
                $urls = array();

                foreach ($links as $node) {
                    $result = $node->getAttribute('href');
                    //Remove the / from the result
                    $urlExtension = preg_replace('/\//', "", $result);
                    $urls[] = $baseURL . $urlExtension . '/';
                }
                return $urls;
            }
        }
        return null;
    }

    /**
     * @param string
     * @return string[]
     */
    public function getAvailableDays($urls)
    {
        $availableDays = array();

        //Experimental
        for($i = 0; $i < sizeof($urls); $i++) {
            $availableDays[] = $this->getCalendarOwnersAvailableDays($urls[$i]);
        }
        return $availableDays;
    }

    /** 
     * @param string
     * @return string Request results
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
     * Work in progress
     *
     * @param string
     * @return string[] A calendar owner's available days
     */
    private function getCalendarOwnersAvailableDays($url)
    {
        //Remove the last / because that messed things up
        $url = rtrim($url, '/');
        echo "<p>$url</p>";

        $data = $this->curlGetRequest($url);

        if ($data != null) {
            $dom = new \DOMDocument();

            if ($dom->loadHTML($data)) {
                $availableDays = array();
                $days = $dom->getElementsByTagName("th");
                $statuses = $dom->getElementsByTagName("td");

                for ($i = 0; $i < $days->length; $i++) {
                    //Convert to lower case to handle inconsistency
                    if (strtolower($statuses->item($i)->nodeValue) == "ok") {
                        $availableDays[] = $days->item($i)->nodeValue;
                    }
                }
                return $availableDays;
            }
        }
        return null;
    }
}