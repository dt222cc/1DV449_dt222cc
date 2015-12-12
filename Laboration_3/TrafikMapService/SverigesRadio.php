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

        // Try to get some new traffic data if the cache is older than 'x' minutes
        if (time() - filemtime(self::$filePath) > 60 * 30) {
            $traffic = $this->getTraffic();

            // Create or update the traffic cache
            if ($traffic !== null) {
                $cache = fopen(self::$filePath, 'w');
                fwrite($cache, $traffic);
                fclose($cache);
            }
        }
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
