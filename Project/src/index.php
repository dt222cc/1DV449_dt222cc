<?php

// INCLUDE THE FILES NEEDED...
require_once("Settings.php");
require_once("Models/TravelForecastModel.php");
require_once("Views/LayoutView.php");
require_once("Views/TravelForecastView.php");
require_once("Controllers/MasterController.php");

// Just a quick soltion to get on timezone to match and get rid of the warning message
date_default_timezone_set("Europe/Stockholm");

// DISPLAY ERRROR MESSAGES, DO DISABLE ON PUBLIC SERVER IF "READY"...
error_reporting(E_ALL);
ini_set('display_errors', 'ON');

// APP USES SESSION
session_start();

// INITIATE OBJECTS AND DO DEPENDENCY INJECTION...
$m = new TravelForecastModel();
$v = new TravelForecastView($m);
$c = new MasterController($m, $v);

// START SERVICE
$c->doTravelForecastService();

// GENERATE THE OUTPUT...
$lv = new LayoutView();
$lv->render($v);

// Trying to pass localstorage values to php, no success so far
echo "<br><pre>";

echo '<br>Session Locations: ';
var_dump($_SESSION['locations']);

echo '<br>Session Forecasts: ';
var_dump($_SESSION['forecasts']);

echo "</pre>";