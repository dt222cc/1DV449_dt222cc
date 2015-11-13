<?php

class ScraperController
{

    private static $urlsFromBaseURL = "Scraper::UrlsFromBaseURL";
    private static $availableMovies = "Scraper::AvailableMovies";

    /**
     * @var Scraper
     * @var ScraperView
     */
    private $model;
    private $view;

    /**
     * @param models/Scraper
     * @param views/ScraperView
     */
    public function __construct(Scraper $m, ScraperView $v)
    {
        $this->model = $m;
        $this->view = $v;
    }

    /**
     * The Web agent scraper
     */
    public function doWebsiteScraperService()
    {
        try {
            if ($this->view->userWantsToScrape()) {
                /* Do the initial work, get URLs */
                $baseURL = $this->view->getURLToScrape();
                $_SESSION[self::$urlsFromBaseURL] = $this->model->getURLs($baseURL);
                if (empty($_SESSION[self::$urlsFromBaseURL])) {
                    throw new InvalidURLException();
                }

                /* Calendar Scrape */
                $calendarOwnersURLs = $this->model->getURLs($_SESSION[self::$urlsFromBaseURL][0]);
                $availableDays = $this->model->getAvailableDays($calendarOwnersURLs);
                if (empty($availableDays)) {
                    throw new NoAvailableDaysException();
                }

                /* Theater Scrape (If there's available days) */
                $_SESSION[self::$availableMovies] = $this->model->getAvailableMovies($_SESSION[self::$urlsFromBaseURL][1], $availableDays);
                if (empty($_SESSION[self::$availableMovies])) {
                    throw new NoAvailableMoviesException();
                }
            }
            /* Restaurant Scrape */
            if ($this->view->userWantsToBook()) {
                $availableTables = $this->model->getAvailableTables($_SESSION[self::$urlsFromBaseURL][2], $_SESSION[self::$availableMovies]);
                var_dump($availableTables);
            }
        }
        catch (InvalidURLException $e) {
            $this->view->setURLInvalid();
        }
        catch (NoAvailableDaysException $e) {
            $this->view->setNoAvailableDays();
        }
        catch (NoAvailableMoviesException $e) {
            $this->view->setNoAvailableMovies();
        }
        catch (Exception $e) {
            $this->view->setUnexpectedErrorOccurred();
        }
    }
}