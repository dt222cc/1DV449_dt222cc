<?php

require_once('HTMLView.php');
require_once('MashupController.php');

$v = new HTMLView();
$c = new MashupController();

// Retrieve traffic from cache or directly from the WebService (SverigesRadio)
$traffic = $c->getTraffic();

// Render the OUTPUT
$v->render($traffic);