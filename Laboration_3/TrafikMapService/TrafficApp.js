var TrafficApp = {
    map: {},
    incidents: [],
    markers: [],
    categories: [ "Alla kategorier", "Vägtrafik", "Kollektivtrafik", "Planerad störning", "Övrigt" ],
    months: [ "Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December" ],

    init: function() {
        // Add the map
        TrafficApp.renderTrafficMap();
        // Populate the filter list
        TrafficApp.populateCategoryFilter();

        // Get traffic from cache and...
        $.ajax('app_data/traffic-information.json')
        .done(function(json) {
            var incidents = json.messages;
            // Render incident markers on the map
            TrafficApp.renderMarkers(incidents);
            // Render traffic list
            TrafficApp.renderTrafficList(incidents);
            // Add some click events
            TrafficApp.addClickEvents(incidents);
        });
    },

    /**
     * Render/populate the map
     */
    renderTrafficMap: function() {
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
     * Render/populate the select dropdown with categories
     */
    populateCategoryFilter: function() {
        var selectHTML = "";

        for (var i = 0; i < TrafficApp.categories.length; i++) {
            selectHTML += "<option value='" + i + "'>" + TrafficApp.categories[i] + "</option>";
        };

        $('#filter').append(selectHTML);
    },

    /**
     * Retrieve traffic information from cache and start adding markers to the map
     *
     * @param array[]
     */
    renderMarkers: function(incidents) {
        incidents.forEach(function(incident) {
            // Add markers
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
            var popupText = title + subcategory + createddate + exactlocation + description;

            // Bind text to a popup
            marker.bindPopup(popupText);
            // Add marker to the map
            marker.addTo(TrafficApp.map);

            // View incident in the list and zoom in
            marker.on('click', function(e) {
                TrafficApp.map.setView([incident.latitude, incident.longitude], 14);

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
        });
    },

    /**
     * Render/populate the traffic list
     *
     * @param array[]
     */
    renderTrafficList: function(incidents) {
        // I'm going to convert the php code responsible for this here

        // Hide the incident details after rendering the list
        $('.incident-details').hide();
    },

    /**
     * Add functionality to hide/show incident details and view the incident on the map
     *
     * @param array[]
     */
    addClickEvents: function(incidents) {
        // Show incident details when clicking an incident title and interact with the map
        $('.incident').on('click', function(event) {
            event.preventDefault();
            $('.incident-details').hide();
            $(this).next().slideDown('fast');

            // Get some values for zoom
            var latitude;
            var longitude;
            var title = $(this).html();
            incidents.forEach(function(incident) {
                if (incident.title === title) {
                    latitude = incident.latitude;
                    longitude = incident.longitude;
                }
            });
            // Zoom map
            TrafficApp.map.setView([latitude, longitude], 14);

            // Open the popup that matches with the title on the traffic list
            TrafficApp.markers.forEach(function(marker) {
                if(marker._popup._content.indexOf(title) >= 0) {
                    marker.openPopup();
                }
            });
        });

        // Reset view handler
        $('#reset').on('click', function() {
            // Set default location and zoom
            TrafficApp.map.setView([60, 15], 6);
            // Set default select value. 4 = "Alla kategorier"
            $('#filter').val(0);
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
     * Format date
     *
     * @param string "/Date(1449809907837+0100)/""
     * @return string "11 Dec 2015"
     */
    parseDate: function(date) {
        // Remove "/Date()/" from the string
        date = date.substr(6, 18);

        // New date with custom format
        date = new Date(parseInt(date));

        return date.getDate() + " " + TrafficApp.months[date.getMonth()] + " " + date.getFullYear();
    },
}

window.onload = new TrafficApp.init();
