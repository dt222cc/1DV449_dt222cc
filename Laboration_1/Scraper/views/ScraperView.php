<?php

class ScraperView
{
    private static $scraperURL = "ScraperView::URL";
    private static $scraperSubmit = "ScraperView::Submit";
    
    /**  @return string HTML  */
	public function getScraperForm()
	{
		return "
		    <form method='post'>
                <label for='" . self::$scraperURL . "'>Ange url: </label>
                <input type='text' name='" . self::$scraperURL . "'>
                <input type='submit' name='" . self::$scraperSubmit . "' value='Start!'>
            </form>
        ";
	}
	
	/** @return boolean */
	public function userWantsToScrape()
	{
	    return isset($_POST[self::$scraperSubmit]);
	}
	
	/** @return string URL | null */
	public function getURLToScrape()
	{
	    return isset($_POST[self::$scraperURL]) ? $_POST[self::$scraperURL] : null;
	}
}