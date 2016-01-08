<?php

class ForecastDAL
{
    public function getForecast($lat, $lng, $datetime)
    {
        $forecast = null;

        $conn = $this->establishConnection();
        if ($stmt = $conn->prepare("SELECT * FROM travelapp_forecasts WHERE (lat = ? AND lng = ? AND forecast_time = ?)")) {
            $stmt->bind_param('sss', $lat, $lng, $datetime);
            $stmt->execute();
            $stmt->bind_result($id, $location, $lat, $lng, $forecastTime, $temperature, $icon, $description);
            while ($stmt->fetch()) {
                $forecast =  (object) [ "datetime", $forecastTime, "temperature" => $temperature, "icon" => $icon, "description" => $description ];
            }
        }
        $conn->close();

        if ($forecast !== null) {
            echo 'Forecast was found! ';
        }
        return $forecast;
    }

    // Issues here might be sql-injection, from the API
    public function saveForecasts($lat, $lng, $forecasts)
    {
        $sql = "INSERT INTO travelapp_forecasts (location, lat, lng, forecast_time, temperature, icon, description) VALUES ";

        // Prepare query for multiple entries
        foreach ($forecasts as $forecast)
        {
            $sql .= "('$forecast->location', '$lat', '$lng', '$forecast->datetime', $forecast->temperature, '$forecast->icon', '$forecast->description'), ";
        }
        // Remove the trailing ", "
        $sql = rtrim($sql, ", ");

        $conn = $this->establishConnection();
        if ($conn->query($sql) === false) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }

    private function establishConnection()
    {
         // Create and check connection
        $conn = new mysqli(Settings::HOST, Settings::USER, Settings::PASSWORD, Settings::SCHEMA);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}