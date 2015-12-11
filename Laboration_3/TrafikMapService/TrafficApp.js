var TrafficApp = {
    map: {},
    markers: [],
    incidents: [],

    init: function() {
        TrafficApp.addResetViewEvent();
        TrafficApp.addHiddenIncidentDetails();
        TrafficApp.addTrafficMap();
        TrafficApp.addMarkers();
    },

    /**
     * Reset view
     */
    addResetViewEvent: function() {
        $('#reset').on('click', function() {
            // Set default location and zoom
            TrafficApp.map.setView([60, 15], 6);
            // Set default select value. 4 = "Alla kategorier"
            $('#filter').val(4);
            // Only close the button if it exists
            var closeButton = $(".leaflet-popup-close-button")[0];
            if (closeButton != undefined) {
                closeButton.click();
            }
            // Hide incident details
            $('.incident-details').hide();
        });
    },

    /**
     * Add functionality to hide/show incident details and view the incident on the map
     */
    addHiddenIncidentDetails: function() {
        // Hide the incident details
        $('.incident-details').hide();

        // Show incident details when clicking an incident title
        $('.incident').on('click', function(event) {
            event.preventDefault();
            $('.incident-details').hide();
            $(this).next().slideDown('fast');

            // Also zoom map
            var latitude;
            var longitude;
            var title = $(this).html();
            TrafficApp.incidents.forEach(function(incident) {
                if (incident.title === title) {
                    latitude = incident.latitude;
                    longitude = incident.longitude;
                }
            });
            TrafficApp.map.setView([latitude, longitude], 14);

            // Also open popup
            TrafficApp.markers.forEach(function(marker){
                if(marker._popup._content.indexOf(title) >= 0) {
                    marker.openPopup();
                }
            });
        });
    },

    /**
     * Populate the map
     */
    addTrafficMap: function() {
        // Initiate a new map
        TrafficApp.map = new L.Map('map');

        // Create the tile layer with correct attribution
        var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var osmAttrib = 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
        var osm = new L.TileLayer(osmUrl, { minZoom: 5, maxZoom: 16, attribution: osmAttrib });
        // Start the map in Sweden
        TrafficApp.map.setView(new L.LatLng(60, 15), 6);
        TrafficApp.map.addLayer(osm);
    },

    /**
     * Retrieve traffic information from cache and start adding markers to the map
     */
    addMarkers: function() {
        var incidents = [];
        // Get traffic from cache
        var traffic = $.ajax('app_data/traffic-information.json');
        // When the loading has completed, access the json
        traffic.done(function(json) {
            json.messages.forEach(function(incident) {
                // Add to incident list
                incidents.push(incident);
                // Add a marker for each incident
                TrafficApp.addMarker(incident);
            });
            // This list is used for setting map view when clicking on incident title/marker
            TrafficApp.incidents = incidents;
        });
    },

    /**
     * For each incident, add a marker with a popup filled with incident details
     *
     * @param object[]
     */
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

        // Bind text to a popup
        marker.bindPopup(popupText);
        // Add marker to the map
        marker.addTo(TrafficApp.map);

        // View incident in the list aswell
        marker.on('click', function(e) {
            // With split I remove the excess text from the title
            var popupTitle = e.target._popup._content.split("<br>")[0].split("</b>")[1];
            $('.incident').each(function(index, incident) {
                if (incident.text === popupTitle) {
                    // Hide previous incident details
                    $('.incident-details').hide();
                    $(this).next().slideDown('fast');
                }
            });
        });

        // Add markers into a list, used to open popup when clicking on the list
        TrafficApp.markers.push(marker);
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

        return date.getDate() + " " + months[date.getMonth()] + " " + date.getFullYear();
    },
}

window.onload = new TrafficApp.init();
