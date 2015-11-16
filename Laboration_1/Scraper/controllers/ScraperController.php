<?php

class ScraperController
{
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
                $urlsFromBaseURL = $this->model->getURLs($baseURL);
                if (empty($urlsFromBaseURL)) {
                    throw new InvalidURLException();
                }
                $this->view->setURLsFromBaseURL($urlsFromBaseURL);

                /* Calendar Scrape */
                $calendarOwnersURLs = $this->model->getURLs($urlsFromBaseURL[0]);
                $availableDays = $this->model->getAvailableDays($calendarOwnersURLs);
                if (empty($availableDays)) {
                    throw new NoAvailableDaysException();
                }

                /* Theater Scrape (If there's available days) */
                $availableMovies = $this->model->getAvailableMovies($urlsFromBaseURL[1], $availableDays);
                if (empty($availableMovies)) {
                    throw new NoAvailableMoviesException();
                }
                $this->view->setAvailableMovies($availableMovies);
            }
            /* Dinner Scrape */
            if ($this->view->userWantsToBook()) {
                $index = $this->view->getMovieParam();
                $movies = $this->view->getAvailableMovies();
                $userPickedMovie = $movies[$index];
                $urls = $this->view->getURLsFromBaseURL();
                $dinnerURL = $urls[2];
                $availableTables = $this->model->getAvailableTables($dinnerURL, $userPickedMovie);
                $this->view->setAvailableTables($availableTables);
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