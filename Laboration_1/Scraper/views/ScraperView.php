<?php

class ScraperView
{
    /** Pre-fill URL for getting started faster (Note remove later on) */
    private static $baseURL = "http://localhost:8080/";

    /** Static variables for the ScraperForm */
    private static $scraperURL = "ScraperView::URL";
    private static $scraperSubmit = "ScraperView::Submit";
    private static $tableURL = "table";

    /** View States (Error messages) */
    private $urlInvalid = false;
    private $unexpectedError = false;
    private $noAvailableDaysError = false;
    private $noAvailableMoviesError = false;

    /**
     * Dependency injection
     *
     * @var /models/Scraper.php
     */
    private $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * View state setters
     */
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

    /**
     * Returns the URL base url
     *
     * @return string URL | null */
    public function getURLToScrape()
    {
        return isset($_POST[self::$scraperURL]) ? $_POST[self::$scraperURL] : null;
    }

    /**
     * To detect user input/action (start crawling)
     *
     * @return boolean
     */
    public function userWantsToScrape()
    {
        return isset($_POST[self::$scraperSubmit]);
    }

    /**
     * To detect if user wants to book (view available tables for specific movie)
     *
     * @return boolean
     */
    public function userWantsToBook()
    {
        return isset($_GET[self::$tableURL]);
    }

    /**
     * Presents the correct view
     *
     * @return string HTML
     */
    public function getResponse()
    {
        //3. Show booking form (from #2)
        if ($this->userWantsToBook()) {
            return $this->getBookingForm();
        }
        //2. Default with available movie list
        if ($this->userWantsToScrape() && !empty($this->model->getMovies())) {
            return $this->getScraperForm() . $this->getMovieList();
        }
        //1. Default
        return $this->getScraperForm();
    }

    /**
     * Default view
     *
     * @return string HTML
     */
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

    /**
     * Available movies presentation
     *
     * @return string HTML
     */
    public function getMovieList()
    {
        $movies = $this->model->getMovies();
        $movieListHTML = "";

        foreach($movies as $movie) {
            $movieListHTML .= "<li>Filmen <strong>" . $movie['name'] . "</strong> klockan "
                . $movie['time'] . " p&aring; " . strtolower($movie['day']);
            $movieListHTML .= " <a href='" . $_SERVER["REQUEST_URI"] . "?" . self::$tableURL
                . "'>V&auml;lj denna och boka bord</a></li>";
        }

        return "
            <h2>F&ouml;ljande filmer hittades</h2>
            <ul>
                $movieListHTML
            </ul>
        ";
    }

    /**
     * Available tables presentation (booking)
     *
     * @return string HTML
     */
    private function getBookingForm()
    {
        return "
            <div>The Booking Form</div>
        ";
    }

    /**
     * Display the proper error message depending on the situation
     *
     * @return string HTML
     */
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