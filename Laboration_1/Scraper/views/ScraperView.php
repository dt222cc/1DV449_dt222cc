<?php

class ScraperView
{
    /** Pre-fill URL for getting started faster (Note remove later on) */
    private static $baseURL = "http://localhost:8080/";

    /** Static variables for the ScraperForm */
    private static $scraperURL = "ScraperView::URL";
    private static $scraperSubmit = "ScraperView::Submit";

    /** Error Messages, Start */
    private $urlInvalid = false;
    private $unexpectedError = false;
    private $noAvailableDaysError = false;
    private $noAvailableMoviesError = false;

    public function setURLInvalid()
    {
        $this->urlInvalid = true;
    }

    public function setUnexpectedErrorOccurred()
    {
        $this->unexpectedError = true;
    }

    public function setNoAvailableDays()
    {
        $this->noAvailableDaysError = true;
    }

    public function setNoAvailableMovies()
    {
        $this->noAvailableMoviesError = true;
    }

    /** Error Messages, End */

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
            <div class='text-danger'>" . $this->getErrorMessage() . "</div>
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
        if ($this->urlInvalid) {
            return "Failed to retrieve the specified URL.";
        }
        if ($this->unexpectedError) {
            return "An unexpected error occurred, try again later.";
        }
        if ($this->noAvailableDaysError) {
            return "There's no matching available days.";
        }
        if ($this->noAvailableMoviesError) {
            return "There's no available movies, it's packed :(";
        }
        return "";
    }
}