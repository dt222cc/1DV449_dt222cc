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
        <link rel='stylesheet' href='css/design.css' />
        <link rel='stylesheet' href='lib/leaflet.css' />
    </head>

    <body>
        <noscript>
          This page needs JavaScript activated to work.
          <style>div { display:none; } </style>
        </noscript>
        <div id='container'>
            <div id='map'>
            </div>
            <div>
                <p>Trafik händelser hämtade den $fileDate</p>
            </div>
            <div>
                <button id='reset-btn'>Återställ</button>
            </div>
            <div id='traffic-list-container'>
                Välj kategori:
                <select id='filter'>
                </select>
                <ul id='traffic-list'>
                </ul>
            </div>
        </div>
        <script src='lib/leaflet.js'></script>
        <script src='lib/jquery-1.11.3.min.js'></script>
        <script src='TrafficApp.js'></script>
    </body>
</html>";
    }
}