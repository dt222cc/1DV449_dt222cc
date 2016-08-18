<?php

class LocationDAL
{
    /**
     * Get a location with matching name (can be issues, haven't really looked into the geonames structure or rather how to use it properly)
     *
     * @param string
     * @return object | null
     */
    public function getLocation($locationName)
    {
        $location = null;

        $conn = $this->establishConnection();
        if ($stmt = $conn->prepare("SELECT * FROM travelapp_locations WHERE name = ? LIMIT 1")) {
            $stmt->bind_param('s', $locationName);
            $stmt->execute();
            $stmt->bind_result($id, $toponymName, $name, $lat, $lng);
            while ($stmt->fetch()) {
                $location =  (object) [ "toponymName" => $toponymName, "name" => $locationName, "lat" => $lat, "lng" => $lng ];
            }
        }
        $conn->close();
        return $location;
    }

    /**
     * Multiple insertion query
     *
     * @param object
     * @return boolean
     */
    public function saveLocation($location)
    {
        $conn = $this->establishConnection();

        if ($stmt = $conn->prepare("INSERT INTO travelapp_locations (toponym_name, name, lat, lng) VALUES (?, ?, ?, ?)")) {
            $stmt->bind_param('ssss', $location->toponymName, $location->name, $location->lat, $location->lng);
            $stmt->execute();
            $conn->close();
            return true;
        }
        $conn->close();
        return false;
    }

    /**
     * @return mysqli connetion
     */
    private function establishConnection()
    {
         // Create and check connection
        $conn = new mysqli(Settings::HOST, Settings::USER, Settings::PASSWORD, Settings::SCHEMA);
        if ($conn->connect_error) {
            printf("Connect failed: %s\n", $conn->connect_error);
            exit();
        }
        $conn->set_charset("utf8");
        return $conn;
    }
}