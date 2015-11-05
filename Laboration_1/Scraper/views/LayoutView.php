<?php

class LayoutView
{
	public function __construct()
	{
		date_default_timezone_set("Europe/Stockholm");
	}
	
	/**
	 * Render the Layout
	 */
	public function render(ScrapeView $scrapeView)
	{
		echo "<!DOCTYPE html>
			<html>
    			<head>
    				<meta charset='utf-8'>
    				<title>A1-dt222cc</title>
    			</head>
    			<body>
    				<div class='container'>
    				    " . $scrapeView->getScrapeForm() . "
    				</div>
				</body>
			</html>
		";
	}
}