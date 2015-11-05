<?php

// INCLUDE THE FILES NEEDED...
require_once("views/LayoutView.php");
require_once("views/ScrapeView.php");


$sv = new ScrapeView();

// GENERATE THE OUTPUT
$lv = new LayoutView();
$lv->render($sv);