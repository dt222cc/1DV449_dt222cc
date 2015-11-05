<?php

class ScrapeView
{
    private static $scrapeURL = "ScrapeView::URL";
    private static $scrapeSubmit = "ScrapeView::Submit";
    
    /**  @return string HTML  */
	public function getScrapeForm()
	{
		return "
		    <form method='post'>
                <label for='" . self::$scrapeURL . "'>Ange url: </label>
                <input type='text' name='" . self::$scrapeURL . "'>
                <input type='submit' name='" . self::$scrapeSubmit . "' value='Start!'>
            </form>
        ";
	}
}