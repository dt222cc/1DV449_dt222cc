<?php

class ForecastDAL
{
    /**
     * Get a forecast with matching coordinates and datetime
     *
     * @param object, string
     * @return object | null
     */
    public function getForecast($location, $datetime)
    {
        $forecast = null;

        $conn = $this->establishConnection();
        if ($stmt = $conn->prepare("SELECT * FROM travelapp_forecasts WHERE (lat = ? AND lng = ? AND forecast_time = ?)")) {
            $stmt->bind_param('sss', $location->lat, $location->lng, $datetime);
            $stmt->execute();
            $stmt->bind_result($id, $forecastLocation, $lat, $lng, $forecastTime, $temperature, $icon, $description);
            while ($stmt->fetch()) {
                $forecast =  (object) [
                    "locationName" => $location->name,
                    "forecastTime" => $forecastTime,
                    "temperature" => $temperature,
                    "icon" => $icon,
                    "description" => $description
                ];
            }
        }
        $conn->close();

        return $forecast;
    }

    /**
     * Multiple insertion query, note: security
     *
     * @param object, object[]
     * @return boolean
     */
    public function saveForecasts($location, $forecasts)
    {
        $re = true;
        $sql = "INSERT INTO travelapp_forecasts (location, lat, lng, forecast_time, temperature, icon, description) VALUES ";

        // Prepare query for multiple entries
        foreach ($forecasts as $forecast)
        {
            $sql .= "('$location->toponymName', '$location->lat', '$location->lng', '$forecast->forecastTime',
             $forecast->temperature, '$forecast->icon', '$forecast->description'), ";
        }
        // Remove the trailing ", "
        $sql = rtrim($sql, ", ");

        $conn = $this->establishConnection();
        if ($conn->query($sql) === false) {
            // echo "Error: " . $sql . "<br>" . $conn->error;
            $re = false;
        }
        $conn->close();
        return $re;
    }

    /**
     * @return mysqli connetion
     */
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