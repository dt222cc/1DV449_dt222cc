<?php

class SverigesRadio
{
	private static $filePath = 'app_data/traffic-information.json';

	public function getTrafficMessages() {
		// Use the traffic information from cache or get some new traffic with a new request if the file is older than 'x' minutes
		if (time() - filemtime(self::$filePath) < 60 * 1) { // Remember to change this
			echo "USE CACHE<br>";
			$traffic = file_get_contents(self::$filePath); // Get the content of the JSON file
		}
		else {
			echo "NEW TRAFFIC<br>";
			$traffic = $this->getTraffic();

			//Create or update the file that contains the traffic information
			if ($traffic !== null) {
				$this->cache = fopen(self::$filePath, 'w');
				fwrite($this->cache, $traffic);
				fclose($this->cache);
			}
		}

		$traffic = json_decode($traffic, true); // Decode the JSON into an associative array

		//Need some kind of for-loop and need to build proper traffic messages from this data
		var_dump($traffic["messages"]);
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
