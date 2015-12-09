<?php

require_once('HTMLView.php');
require_once('MashupController.php');

$v = new HTMLView();
$c = new MashupController();

$c->doMashup();
$v->render();


//Note: Format messages
//Note: Messages to View (List, filter)
//Note: Work on maps (OpenStreetMap or GoogleMaps)