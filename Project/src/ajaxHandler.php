<!-- This file is used in conjunction with app.js ajax post to pass on the localstorage to PHP -->

<?php

session_start(); // Might not be needed

$_SESSION["locations"] = $_POST['locations'];
$_SESSION["forecasts"] = $_POST['forecasts'];