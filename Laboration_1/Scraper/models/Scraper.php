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

        //Invalid URL
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

        return call_user_func_array('array_intersect', $availableDays);
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
}