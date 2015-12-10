<?php

require_once('TrafficMessage.php');

class SverigesRadio
{
	private static $filePath = 'app_data/traffic-information.json';
	private $messages = array();

	/**
	 * Retrieves traffic from webservice or cache, create/update cache, format traffic incidents/messages
	 *
	 * @return array
	 */
	public function getTrafficMessages() {
		// Use the traffic information from cache or get some new traffic with a new request if the file is older than 'x' minutes
		if (time() - filemtime(self::$filePath) < 60 * 30) {
			echo "USE CACHE<br>";
			$traffic = file_get_contents(self::$filePath);
		}
		else {
			echo "NEW TRAFFIC<br>";
			$traffic = $this->getTraffic();

			// Create or update the file that contains the traffic information
			if ($traffic !== null) {
				$this->cache = fopen(self::$filePath, 'w');
				fwrite($this->cache, $traffic);
				fclose($this->cache);
			}
			// Use cache if no response from SverigesRadio
			else {
				$traffic = file_get_contents(self::$filePath);
			}
		}
		// Decode the JSON into an associative array
		$traffic = json_decode($traffic, true);
		//Format messages as objects inside an array
		foreach ($traffic["messages"] as $message) {
			$this->messages[] = new TrafficMessage($message);
		}

		return $this->messages;
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
