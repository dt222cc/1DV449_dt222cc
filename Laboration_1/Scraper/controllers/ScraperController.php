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
                try {
                    //1. Calendar Scrape
                    echo "<p>Scrape: $urls[0] </p>";
                    $this->doCalendarScrape($urls[0]);

                    //2. Theater Scrape
                    //echo "Scrape: " . $urls[1] . "<br>";

                    //3. Restaurant Scrape
                    //echo "Scrape: " . $urls[2] . "<br>";
                }
                catch (Exception $e) {
                    $this->view->setUnexpectedErrorOccurred();
                }
            }
        }
    }

    /**
     * @param string
     * @return bool
     * @throws Exception
     */
    private function doCalendarScrape($url)
    {
        $calendarOwnerURLs = $this->model->getURLs($url);

        $availableDays = $this->model->getAvailableDays($calendarOwnerURLs);

        if ($availableDays == null) {
            throw new Exception();
        }

        var_dump($availableDays);
        //Need to figure out how to keep days that match
        //Cannot find anything on intersect with arrays inside an array
    }
}