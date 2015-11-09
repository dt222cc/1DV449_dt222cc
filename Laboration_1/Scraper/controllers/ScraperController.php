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
        if ($this->view->userWantsToScrape()) {
            //1. Do the initial work, get URLs
            $baseURL = $this->view->getURLToScrape();
            $urls = $this->model->getURLs($baseURL);

            //2. If URL was invalid
            if ($urls == null) {
                $this->view->setURLFailed();
            }
            //3. Do some scraping
            else {
                //1. Calendar Scrape
                echo "<p>Scrape: $urls[0] </p>";
                $this->doCalendarScrape($urls[0]);

                //2. Theater Scrape
//                echo "Scrape: " . $urls[1] . "<br>";

                //3. Restaurant Scrape
//                echo "Scrape: " . $urls[2] . "<br>";
            }
        }
    }

    private function doCalendarScrape($url)
    {
        $calendarOwnerURLs = $this->model->getURLs($url);

        $availableDays = $this->model->getAvailableDays($calendarOwnerURLs);

        //Tests
        if ($availableDays) {
            echo "<p>Ok</p>";
        } else {
            echo "<p>Fail</p>";
        }
    }
}