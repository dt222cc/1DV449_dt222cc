<?php

class ScraperController
{
    /**
     * @var models/Scraper
     * @var views/ScraperView
     */
    private $model;
    private $view;
    
    /**
     * @param models/Scraper
     * @param views/ScraperView
     */
    public function __construct(Scraper $scraper, ScraperView $scraperView)
    {
        $this->model = $scraper;
        $this->view = $scraperView;
    }
    
    /**
     * The Web agent scraper
     */
    public function doWebsiteScraperService()
    {
        if ($this->view->userWantsToScrape()) {
            echo "OK so far.";
        }
    }
}