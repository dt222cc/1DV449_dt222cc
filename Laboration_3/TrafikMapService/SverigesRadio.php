<?php

class SverigesRadio
{
    private static $filePath = 'app_data/traffic-information.json';

    /**
     * Retrieves traffic from webservice or cache, create/update cache, format traffic incidents/messages
     *
     * @return array
     */
    public function getTrafficMessages() {
        $messages = array();

        // Use the traffic information from cache or get some new traffic with a new request if the file is older than 'x' minutes
        if (time() - filemtime(self::$filePath) < 60 * 30) {
            echo "USING CACHE<br>";
            $traffic = file_get_contents(self::$filePath);
        }
        else {
            echo "NEW TRAFFIC<br>";
            $traffic = $this->getTraffic();

            // Create or update the file that contains the traffic information
            if ($traffic !== null) {
                $cache = fopen(self::$filePath, 'w');
                fwrite($cache, $traffic);
                fclose($cache);
            }
            // Use cache if no response from SverigesRadio
            else {
                echo "USING CACHE<br>";
                $traffic = file_get_contents(self::$filePath);
            }
        }

        // Decode the JSON into an associative array
        $traffic = json_decode($traffic, true);

        return $traffic["messages"];
    }

    /**
     * http://sverigesradio.se/api/documentation/v2/metoder/trafik.html
     *
     * @return JSON
     */
    private function getTraffic() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.sr.se/api/v2/traffic/messages?format=json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
