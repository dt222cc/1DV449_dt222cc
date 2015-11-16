<?php

class ScraperView
{
    /** Static variables for the ScraperForm */
    private static $scraperURL = "ScraperView::URL";
    private static $scraperSubmit = "ScraperView::Submit";
    private static $urlsFromBaseURL = "Scraper::UrlsFromBaseURL";
    private static $availableMovies = "Scraper::AvailableMovies";
    private static $availableTables = "Scraper::AvailableTables";
    private static $tableURL = "table";
    private static $movieParam = "movie";

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
     * View state setters (error handling)
     */
    public function setURLInvalid()
    {
        $this->urlInvalid = true;
        $this->unsetSessions(); //Bad URL
    }

    public function setUnexpectedErrorOccurred()
    {
        $this->unexpectedError = true;
        $this->unsetSessions();
    }

    public function setNoAvailableDays()
    {
        $this->noAvailableDaysError = true;
        $this->unsetSessions();
    }

    public function setNoAvailableMovies()
    {
        $this->noAvailableMoviesError = true;
        $this->unsetSessions();
    }

    /**
     * Setters
     *
     * @param array
     */
    public function setURLsFromBaseURL($urls)
    {
        $_SESSION[self::$availableMovies] = $urls;
    }

    public function setAvailableMovies($movies)
    {
        $_SESSION[self::$availableMovies] = $movies;
    }

    public function setAvailableTables($tables)
    {
        $_SESSION[self::$availableTables] = $tables;
    }

    /**
     * Getters
     *
     * @return array | null
     */
    public function getURLsFromBaseURL()
    {
        return isset($_SESSION[self::$urlsFromBaseURL]) ? $_SESSION[self::$urlsFromBaseURL] : null;
    }

    public function getAvailableMovies()
    {
        return isset($_SESSION[self::$availableMovies]) ? $_SESSION[self::$availableMovies] : null;
    }

    public function getAvailableTables()
    {
        return isset($_SESSION[self::$availableTables]) ? $_SESSION[self::$availableTables] : null;
    }

    /**
     * Return the movie param from URL (01 | 02 | 03)
     *
     * @return string | null
     */
    public function getMovieParam()
    {
        return isset($_GET[self::$movieParam]) ? $_GET[self::$movieParam] : null;
    }

    /**
     * Returns the URL base url.
     * Add http:// and/or a trailing / if not present. Also replace ; with :
     *
     * @return string URL */
    public function getURLToScrape()
    {
        $url = "";
        if (isset($_POST[self::$scraperURL])) {
            $url = $_POST[self::$scraperURL];
            if (strpos($url, 'http://') === false){
                $url = 'http://' . $url;
            }
            $url = preg_replace('/\;/', ":", $url);
            $url = rtrim($url, '/') . "/";
        }
        return $url;
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
        //Checks if 'table' is in the url
        return strpos($_SERVER["REQUEST_URI"], self::$tableURL);
    }

    /**
     * Presents the correct view
     *
     * @return string HTML
     */
    public function getResponse()
    {
        //3. Show booking form (from #2 the movie list)
        if ($this->userWantsToBook()) {
            return $this->getBookingForm();
        }
        //2. With available movie list
        if ($this->userWantsToScrape()) {
            return $this->getMovieList();
        }
        //1. Default
        return $this->getScraperForm();
    }

    /**
     * Default view
     *
     * @return string HTML
     */
    private function getScraperForm()
    {
        return "
            <div class='text-danger'>" . $this->getErrorMessage() . "</div>
            <form method='post'>
                <label for='" . self::$scraperURL . "'>Ange url: </label>
                <input type='text' name='" . self::$scraperURL . "' value='" . $this->getURLToScrape() . "'>
                <input type='submit' name='" . self::$scraperSubmit . "' value='Start!'>
            </form>
        ";
    }

    /**
     * Available movies presentation
     *
     * @return string HTML
     */
    private function getMovieList()
    {
        $formHTML = $this->getScraperForm();
        $movies = $this->getAvailableMovies();

        //Default html
        if (empty($movies)) {
            return $formHTML;
        }

        $listHTML = "";
        $index = 0;
        foreach($movies as $movie) {
            $day = "";
            if ($movie['day'] == "Friday") {
                $day = "fredag";
            } else if ($movie['day'] == "Saturday") {
                $day = "l&ouml;rdag";
            } else if ($movie['day'] == "Sunday") {
                $day = "s&ouml;ndag";
            }

            $listHTML .= "<li>Filmen <strong>" . $movie['name'] . "</strong> klockan "
                . $movie['time'] . " p&aring; " . $day;
            $listHTML .= " <a href='" . $_SERVER["REQUEST_URI"] . "/" . self::$tableURL
                . "?" . "movie=" . $index++ . "'>V&auml;lj denna och boka bord</a></li>";
        }

        $movieListHTML = "
            <h2>F&ouml;ljande filmer hittades</h2>
            <ul>
                $listHTML
            </ul>
        ";
        //With the available movie list
        return $formHTML . $movieListHTML;
    }

    /**
     * Available tables presentation (booking)
     *
     * @return string HTML
     */
    private function getBookingForm()
    {
        $tables = $this->getAvailableTables();
        $index = $this->getMovieParam();
        $movies = $this->getAvailableMovies();
        $movie = $movies[$index];

        //By typing 'table' in the url without submitting a proper URL
        if (empty($movies)) {
            return "<p>Bad request</p>";
        }
        //There is available table/'s
        if (!empty($tables)) {
            $tablesHTML = "";
            foreach ($tables as $table) {
                $time1 = substr($table, 3, 2);
                $time2 = substr($table, 5, 2);

                $tablesHTML .= "<li>Det finns ett ledigt bord mellan klockan " . $time1 . " och " . $time2
                    . " efter att sett filmen ". $movie['name'] . " klockan " . substr($movie['time'], 0, 2) . " </li>";
            }
            return "
                <h2>F&ouml;ljande tider &auml;r lediga att boka p&aring; zekes restaurang</h2>
                <ul>
                    $tablesHTML
                </ul>
            ";
        }
        //There is no available table/'s
        return "
            <p>Det fanns inga lediga tider att boka p&aring; zekes restaurang f&ouml;r filmen "
            . $movie['name'] . " klockan " . substr($movie['time'], 0, 2) . "</p>
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

    /**
     * Unset some session variables if URL is bad, to prevent some presentation
     */
    private function unsetSessions()
    {
        unset($_SESSION[self::$availableMovies], $_SESSION[self::$availableTables]);
    }
}