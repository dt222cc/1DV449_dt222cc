<?php

class LayoutView
{
    /**
     * Render the Layout
     * @param /Views/TravelForecastView
     */
    public function render(TravelForecastView $view)
    {
        echo '
<!DOCTYPE html>
<html lang="sv">
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Weather Comparison</title>
        <link rel="stylesheet" href="Content/bootstrap.min.css" />
        <link rel="stylesheet" href="Content/site.css" />
    </head>
    <body>
        <noscript>
            This page needs JavaScript activated to work.
            <style> div { display:none; } </style>
        </noscript>
        <div class="container">
            ' . $view->getResponse() . '
        </div>
        <script src="Scripts/jquery-1.11.3.min.js"></script>
        <script src="Scripts/app.js"></script>
    </body>
</html>';
    }
}