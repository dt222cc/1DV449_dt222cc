<!-- Retrieve the localstorage data and set the associated session variables -->

<?php

session_start();

unset($_SESSION["locations"]);
unset($_SESSION["forecasts"]);

$locations = $_POST['locations'];
$forecasts = $_POST['forecasts'];

$_SESSION["locations"] = $locations;
$_SESSION["forecasts"] = $forecasts;