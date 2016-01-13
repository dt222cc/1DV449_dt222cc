<?php

session_start();

$_SESSION["locations"] = $_POST['locations'];
$_SESSION["forecasts"] = $_POST['forecasts'];