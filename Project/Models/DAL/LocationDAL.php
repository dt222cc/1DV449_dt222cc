<?php

class LocationDAL
{
    public function getLocation($locationName)
    {
        $location = null;

        $conn = $this->establishConnection();
        if ($stmt = $conn->prepare("SELECT * FROM travelapp_locations WHERE name = ? LIMIT 1")) {
            $stmt->bind_param('s', $locationName);
            $stmt->execute();
            $stmt->bind_result($id, $toponymName, $name, $lat, $lng);
            while ($stmt->fetch()) {
                $location =  (object) [ "toponymName" => $toponymName, "name" => $name, "lat" => $lat, "lng" => $lng ];
            }
        }
        $conn->close();

        if ($location !== null) {
            echo 'Locaton was found! ';
        }
        return $location;
    }

    public function saveLocation($location)
    {
        try {
            $conn = $this->establishConnection();

            if ($stmt = $conn->prepare("INSERT INTO travelapp_locations (toponym_name, name, lat, lng) VALUES (?, ?, ?, ?)")) {
                $stmt->bind_param('ssss', $location->toponymName, $location->name, $location->lat, $location->lng);
                $stmt->execute();
                $conn->close();
                return true;
            }
            $conn->close();
            throw new exception();
        }
        catch (exception $e) {
            return false;
        }
    }

    private function establishConnection()
    {
         // Create and check connection
        $conn = new mysqli(Settings::HOST, Settings::USER, Settings::PASSWORD, Settings::SCHEMA);
        if ($conn->connect_error) {
            printf("Connect failed: %s\n", $conn->connect_error);
            exit();
        }
        return $conn;
    }
}