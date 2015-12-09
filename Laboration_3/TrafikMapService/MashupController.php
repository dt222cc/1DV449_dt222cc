<?php

require_once('SverigesRadio.php');

class MashupController
{
	private $webService;
	private $trafficMessages;

	public function __construct() {
		$this->webService = new SverigesRadio();
	}

	public function doMashup() {
		$this->trafficMessages = $this->webService->getTrafficMessages();
	}
}

//Note: Format messages
//Note: Messages to View