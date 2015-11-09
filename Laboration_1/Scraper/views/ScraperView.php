<?php

class ScraperView
{
    /** Pre-fill URL for getting started faster (Note remove later on) */
    private static $baseURL = "http://localhost:8080/";

    /** Static variables for the ScraperForm */
    private static $scraperURL = "ScraperView::URL";
    private static $scraperSubmit = "ScraperView::Submit";

    /** For error message */
    private $urlFailed = false;

    /** @return void */
    public function setURLFailed()
    {
        $this->urlFailed = true;
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

    /** @return string HTML  */
    public function getScraperForm()
    {
        return "
            " . $this->getErrorMessage() . "
            <form method='post'>
                <label for='" . self::$scraperURL . "'>Ange url: </label>
                <input type='text' name='" . self::$scraperURL . "' value='" . self::$baseURL . "'>
                <input type='submit' name='" . self::$scraperSubmit . "' value='Start!'>
            </form>
        ";
    }

    /** @return string HTML */
    private function getErrorMessage()
    {
        return $this->urlFailed ? "Failed to retrieve the specified URL" : "";
    }
}