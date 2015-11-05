<?php

class Scraper
{
    /**
     * @param string
     * @return string[] URL | null
     */
    public function getSites($baseURL)
    {
        $data = $this->curlGetRequest($baseURL);

        //If URL was valid and return data
        if ($data != null) {
            $dom = new \DOMDocument();
            
            if ($dom->loadHTML($data)) {
                //Get all the links from the site
                $links = $dom->getElementsByTagName("a");
                $urls = array();

                //Store the urls from the site in an array
                foreach ($links as $node) {
                    $urls[] = $node->getAttribute('href');
                }
                return $urls;
            }
        }
        return null;
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
}