<?php

class LayoutView
{
    public function __construct()
    {
        date_default_timezone_set("Europe/Stockholm");
    }

    /**
     * Render the Layout
     */
    public function render(ScraperView $scraperView)
    {
        echo '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel="stylesheet" href="css/style.css">
                    <title>A1-dt222cc</title>
                </head>
                <body>
                    <div class="container">
                        ' . $scraperView->getScraperForm() . '
                        ' . $scraperView->getMovieList() . '
                    </div>
                </body>
            </html>
        ';
    }
}