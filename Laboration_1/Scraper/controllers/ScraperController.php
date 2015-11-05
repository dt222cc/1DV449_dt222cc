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
            $sites = $this->model->getSites($baseURL);

            //2. If URL was invalid
            if ($sites == null) {
                $this->view->setURLFailed();
            }
            //3. Do some scraping
            else {
                //1. Calendar Scrape
                echo "Scrape: " . $sites[0];

                //2. Theater Scrape
                echo "Scrape: " . $sites[1];

                //3. Restaurant Scrape
                echo "Scrape: " . $sites[2];
            }
        }
    }
}