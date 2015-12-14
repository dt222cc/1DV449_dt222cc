<?php

class HTMLView
{
    public function __construct() {
        date_default_timezone_set('Europe/Stockholm');
    }

    /**
     * Render the HTML
     *
     * @param array[] Traffic list
     */
    public function render() {
        $fileDate = date("j M Y H:i:s", filemtime('app_data/traffic-information.json'));

        echo "
<!DOCTYPE html>
<html lang='sv'>
    <head>
        <meta charset='UTF-8'>
        <title>dt222cc - Laboration 3</title>
        <link rel='stylesheet' href='lib/leaflet.css' />
        <link rel='stylesheet' href='lib/bootstrap.min.css' />
        <link rel='stylesheet' href='css/design.css' />
    </head>

    <body>
        <noscript>
          This page needs JavaScript activated to work.
          <style>div { display:none; } </style>
        </noscript>
        <div id='container'>
            <div id='traffic-list'>
                <div id='traffic-header'>
                    <button id='reset-btn' class='btn btn-lg btn-primary'>Återställ</button>
                    <div id='filter-desc'>
                    </div>
                    <select id='filter'>
                    </select>
                    <p>Trafikhändelserna hämtades den $fileDate<br>Händelserna är sorterade efter datum, nyaste först.<br>Nästa hämtning sker som tidigast 15 min efter den senaste hämtningen.</p>
                </div>
                <ul id='traffic-ul'>
                </ul>
            </div>
            <div id='map'>
            </div>
        </div>
        <script src='lib/leaflet.js'></script>
        <script src='lib/jquery-1.11.3.min.js'></script>
        <script src='TrafficApp.js'></script>
    </body>
</html>";
    }
}