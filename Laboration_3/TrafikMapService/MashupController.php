<?php

require_once('SverigesRadio.php');

class MashupController
{
	private $webService;

	public function __construct() {
		$this->webService = new SverigesRadio();
	}

	public function getTraffic() {
		return $this->webService->getTrafficMessages();
	}
}