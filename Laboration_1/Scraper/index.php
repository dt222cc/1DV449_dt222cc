<?php

// INCLUDE THE FILES NEEDED...
require_once("models/Scraper.php");
require_once("views/LayoutView.php");
require_once("views/ScraperView.php");
require_once("controllers/ScraperController.php");
require_once("exceptions/NoAvailableDaysException.php");
require_once("exceptions/InvalidURLException.php");
require_once("exceptions/NoAvailableMoviesException.php");

// APPLICATION USES SESSIONS
session_start();

// INITIATE OBJECTS AND DO DEPENDENCY INJECTION...
$m = new Scraper();
$v = new ScraperView($m);
$c = new ScraperController($m, $v);

// START THE WEB AGENT SCRAPER...
$c->doWebsiteScraperService();

// GENERATE THE OUTPUT...
$lv = new LayoutView();
$lv->render($v);