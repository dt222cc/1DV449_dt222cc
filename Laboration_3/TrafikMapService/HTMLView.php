<?php

class HTMLView
{
	public function __construct() {
		date_default_timezone_set('Europe/Stockholm');
	}

	public function render($traffic) {
	echo "
		<!DOCTYPE html>
		<html lang='sv'>
			<head>
				<meta charset='UTF-8'>
				<title>dt222cc - Laboration 3</title>
				<link rel='stylesheet' href='css/design.css' />
			</head>

			<body>
				<div id='container'>
					<div id='my-list-container'>
						Välj kategori:
						<select id='filter'></select>
						<ul id='list'>
							" . $this->getTrafficView($traffic) . "
						</ul>
					</div>
				</div>
               <script src='lib/jquery-1.11.3.min.js'></script>
               <script src='app.js'></script>
			</body>
		</html>
		";
	}

	/**
	 * Builds the traffic list with the traffic data
	 *
	 * @param array(object)
	 * @return string HTML
	 */
	private function getTrafficView($traffic) {
		$string = "";

		// For each traffic incident add a list-tag with title, date, location and description
		foreach ($traffic as $t) {
			$title = $t->getTitle();
			$date = $t->getCreateddate();
			$date = preg_replace('/\s+/S', " ", $date);

			$category = $t->getSubcategory();
			// These fields can be empty, so I have two functions to handle that
			$location = $this->getLocationHTML($t->getExactlocation());
			$description = $this->getDescriptionHTML($t->getDescription());

			$string .= "
				<li>
					<a class='incident' href='#'>$title</a>
					<p class='incident-details'>
						Datum: $date
						$location
						$description<br>
						Kategori: $category
					</p>
				</li>";
		}

		return $string;
	}

	/**
	 * Helper methods for getTrafficView. Handles empty fields.
	 *
	 * @param string
	 * @return string HTML | null
	 */
	private function getLocationHTML($location) {
		if (trim($location) != "") {
			return "<br>Plats: $location";
		}
		return "";
	}
	private function getDescriptionHTML($description) {
		if (trim($description) != "") {
			return "<br>Beskrivning: $description";
		}
		return "";
	}
}