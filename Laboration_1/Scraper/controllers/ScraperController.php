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
                $urls = $this->model->getURLs($baseURL);
                if (empty($urls)) {
                    throw new InvalidURLException();
                }

                /* Calendar Scrape */
                $calendarOwnersURLs = $this->model->getURLs($urls[0]);
                $availableDays = $this->model->getAvailableDays($calendarOwnersURLs);
                if (empty($availableDays)) {
                    throw new NoAvailableDaysException();
                }

                /* Theater Scrape (If there's available days) */
                $availableMovies = $this->model->getAvailableMovies($urls[1], $availableDays);
                if (empty($availableMovies)) {
                    throw new NoAvailableMoviesException();
                }

                /* Restaurant Scrape */
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