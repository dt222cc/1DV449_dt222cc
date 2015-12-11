var TrafficApp = {

	init: function() {
		var map;

		TrafficApp.addHiddenIncidentDetails();
		TrafficApp.addTrafficMap();
		TrafficApp.addMarkers();
	},

	addHiddenIncidentDetails: function() {
		// Hide the incident details
		$('.incident-details').hide();

		// Show incident details when clicking an incident title
		$('.incident').on('click', function(event) {
			event.preventDefault();
			$('.incident-details').hide();
			var details = $(this).next();
			details.slideDown('fast');
		});
	},

	addTrafficMap: function() {
		// Set up the map
		TrafficApp.map = new L.Map('map');

		// Create the tile layer with correct attribution
		var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
		var osmAttrib = 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
		var osm = new L.TileLayer(osmUrl, {	minZoom: 5, maxZoom: 16, attribution: osmAttrib	});
		// Start the map in Sweden
		TrafficApp.map.setView(new L.LatLng(56.726, 14.513), 7);
		TrafficApp.map.addLayer(osm);
	},

	addMarkers: function() {
		// Get traffic from cache
		var traffic = $.ajax('app_data/traffic-information.json');
		// When the loading has completed, access the json
		traffic.done(function(json) {
			json.messages.forEach(function(incident) {
				// Add a marker for each incident
				TrafficApp.addMarker(incident);
			});
		});

	},

	addMarker: function(incident) {
		// Create marker
		var marker = new L.marker([incident.latitude, incident.longitude]);

		// Parse and format the incident details
		var title = "<b>Titel: </b>" + incident.title.trim();
		var subcategory = "<br><b>Kategori: </b> " + incident.subcategory.trim();

		// Format date
		var createddate = "<br><b>Datum: </b>" + TrafficApp.parseDate(incident.createddate.trim());

		// These two fields can be empty
		var exactlocation = incident.exactlocation.trim();
		exactlocation = incident.exactlocation.trim() != "" ? "<br><b>Plats: </b>" + exactlocation : "";

		var description = incident.description.trim();
		description = description != "" ? "<br><b>Beskrivning: </b>" + description : "";

		// Concatenate the strings into the popup text
		var popupText = title + createddate + exactlocation + description + subcategory;

		// Bind text to popup
		marker.bindPopup(popupText);
		// Bind marker to the map
		marker.addTo(TrafficApp.map);
	},

	/**
	 * Format date
	 *
	 * @param string "/Date(1449809907837+0100)/""
	 * @return string "11 Dec 2015"
	 */
	parseDate: function(date) {
		// Month names, we replace the number from getMonth() with the corresponding month
		var months = [ "Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December" ];

		// Remove "/Date()/" from the string
		date = date.substr(6, 18);

		// New date with custom format
		date = new Date(parseInt(date));
		date = date.getDate() + " " + months[date.getMonth()] + " " + date.getFullYear();

		return date;
	},
}

window.onload = new TrafficApp.init();
