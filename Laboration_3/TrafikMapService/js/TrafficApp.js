'use strict';

var TrafficApp = {
    trafficURL: "http://api.sr.se/api/v2/traffic/messages?pagination=false&format=json",
    fileMaxTime: 15, // minutes
    map: {},
    incidents: [],
    markers: [],
    categories: [ "Alla kategorier", "Vägtrafik", "Kollektivtrafik", "Planerad störning", "Övrigt" ],
    months: [ "Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec" ],

    init: function() {
        // Add the map
        TrafficApp.renderTrafficMap();
        // Populate the filter list
        TrafficApp.populateCategoryFilter();
        // Get traffic from cache or webservice and renders that
        TrafficApp.getTrafficMessages();
    },

    /**
     * Get traffic messages from cache or webservice. Then render the traffic
     * A bit DRY because of "onreadestatechange", not sure how to prevent that.
     */
    getTrafficMessages: function() {
        var incidents;
        var timestamp;

        // Use local storage if traffic is still fresh
        if (TrafficApp.isTrafficFresh() === true) {
            timestamp = JSON.parse(localStorage.getItem('traffic-expire')).timestamp;
            incidents = JSON.parse(localStorage.getItem('traffic'));
            TrafficApp.renderTraffic(incidents, timestamp);
        } else {
            var response;
            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        response = JSON.parse(xhr.responseText);
                        incidents = response.messages;

                        // Timestamp to be able to check file time
                        timestamp = new Date().getTime();
                        var storageTime = { timestamp: timestamp };

                        // Set localstorage, two items
                        localStorage.setItem('traffic', JSON.stringify(incidents));
                        localStorage.setItem("traffic-expire", JSON.stringify(storageTime));

                    } else {
                        // Use localstorage if webservice is down
                        console.log("An error occurred while retrieving new traffic, using traffic from cache.");
                        incidents = JSON.parse(localStorage.getItem('traffic'));
                    }

                    TrafficApp.renderTraffic(incidents, timestamp);
                }
            };
            xhr.open("GET", TrafficApp.trafficURL, true);
            xhr.send(null);
        }
    },

    /**
     * Checks if "cache" exists and if it's still fresh
     *
     * @returns {boolean}
     */
    isTrafficFresh: function() {
        var trafficStorage = localStorage.getItem('traffic');
        var trafficExpire = localStorage.getItem('traffic-expire');

        // Does file exists
        if (trafficStorage !== null && trafficExpire !== null) {
            var dateOfFile = JSON.parse(trafficExpire).timestamp;
            var dateOfToday = new Date().getTime();

            // Is file still fresh
            if (dateOfToday - dateOfFile < TrafficApp.fileMaxTime * 1000 * 60) {
                console.log("Still fresh");
                return true;
            }
        }
        console.log("Not fresh");
        return false;
    },

    renderTraffic: function(incidents, timestamp) {
        // Render incident markers on the map
        TrafficApp.renderMarkers(incidents);
        // Render traffic list
        TrafficApp.renderTrafficList(incidents);
        // Add some click events
        TrafficApp.addClickEvents(incidents);

        var collectionDate = TrafficApp.parseDate(timestamp);
        $('#traffic-header').append("<p>Trafikhändelserna hämtades den " + collectionDate +
            "<br>Händelserna är sorterade efter datum, nyaste först.<br>Nästa hämtning sker som tidigast " +
            TrafficApp.fileMaxTime + " min efter den senaste hämtningen.</p>");
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
     * Retrieve traffic information from cache and start adding markers to the map
     *
     * @param incidents
     */
    renderMarkers: function(incidents) {
        // Remove markers if exists (for filter to work)
        TrafficApp.markers.forEach(function(marker){
            TrafficApp.map.removeLayer(marker);
        });

        // Add markers (incidents have been filtered)
        incidents.forEach(function(incident) {
            // Initiate a new marker with specified coordinates
            var marker = new L.Marker([incident.latitude, incident.longitude]);

            // Parse and format the incident details
            var title = "<b>Titel: </b>" + incident.title;
            var subcategory = "<br><b>Kategori: </b> " + incident.subcategory;

            // Format date
            var createdDate = "<br><b>Datum: </b>" + TrafficApp.parseDate(incident.createddate);

            // These two fields can be empty
            var exactLocation = incident.exactlocation != "" ? "<br><b>Plats: </b>" + incident.exactlocation : "";
            var description = incident.description != "" ? "<br><b>Beskrivning: </b>" + incident.description : "";

            // Concatenate the strings into the popup text
            var popupText = title + createdDate + subcategory + exactLocation + description;

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
     * @param incidents
     */
    renderTrafficList: function(incidents) {
        // Do the sorting, latest first
        incidents.sort(function(a, b) {
            // Turn strings into dates, and then subtract them to get a value that is either negative, positive, or zero
            return new Date(TrafficApp.parseDate(b.createddate)) - new Date(TrafficApp.parseDate(a.createddate));
        });

        var trafficUL = $("#traffic-ul");
        // Re-render list, for filter to work)
        trafficUL.empty();
        // In case there's no incidents for the selected category
        if (incidents === undefined || incidents.length === 0) {
            trafficUL.append("<p'>Det finns inga händelser för denna kategori.</p>");
        } else {
            incidents.forEach(function(incident) {
                var date = "Datum: " + TrafficApp.parseDate(incident.createddate);
                var category = "<br>Händelse: " + incident.subcategory;

                // These two fields can be empty, <br> tags inside these strings
                var location = incident.exactlocation != "" ? "<br>Plats: " + incident.exactlocation : "";
                var description = incident.description != "" ? "<br>Beskrivning: " + incident.description : "";

                var incidentText = date + category + location + description;

                trafficUL.append("<li><a class='incident category" + incident.category + "' href='#'>" + incident.title +
                    "</a><p class='incident-details'>" + incidentText + "</p></li>");
            });
        }

        // Hide the incident details after rendering the list
        $('.incident-details').hide();
    },

    /**
     * Add functionality to hide/show incident details and view the incident on the map
     *
     * @param incidents
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
        $('#reset-btn').on('click', function() {
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

        // Render new traffic list and markers when filter is changed
        $('#filter').on('change', function() {
            var filteredList = [];
            var filter = $('#filter').val();

            incidents.forEach(function(incident) {
                // Keep incident if filter is set to "All".
                if (filter === "0") {
                    filteredList.push(incident);
                }
                // Only keep matching categories
                else if (incident.category === filter - 1) {
                    filteredList.push(incident);
                }
            });
            // Re-render markers and traffic list
            TrafficApp.renderMarkers(filteredList);
            TrafficApp.renderTrafficList(filteredList);
            // Re-apply events (So we can click on list again)
            TrafficApp.addClickEvents(incidents);
        });
    },

    /**
     * Render/populate the select input with categories and a filter description regarding color (css)
     */
    populateCategoryFilter: function() {
        var selectHTML = "";
        var filterDesc = "";
        // Select option
        for (var i = 0; i < TrafficApp.categories.length; i++) {
            selectHTML += "<option value='" + i + "'>" + TrafficApp.categories[i] + "</option>";
            // Description
            if (i < TrafficApp.categories.length - 1) {
                filterDesc += "<p class='category" + i + "'>" + TrafficApp.categories[i + 1] + "</p>";
            }
        }

        $('#filter-desc').append(filterDesc);
        $('#filter').append(selectHTML);
    },

    /**
     * Format date
     *
     * @return string "11 Dec 2015"
     * @param date
     */
    parseDate: function(date) {
        // For traffic collection date
        if (date.toString().length === 13) {
            date = new Date(date);
        }
        // For traffic incidents
        else {
            // Remove "/Date()/" from the string
            date = date.substr(6, 18);
            date = new Date(parseInt(date));
        }

        return date.getDate() + " " + TrafficApp.months[date.getMonth()] + " " + date.getFullYear() + " " +
            TrafficApp.addZeroBefore(date.getHours()) + ":" + TrafficApp.addZeroBefore(date.getMinutes());
    },

    /**
     * Make sure the number has two digits
     *
     * @return string
     * @param n
     */
    addZeroBefore: function(n) {
        return (n < 10 ? '0' : '') + n;
    }
};

window.onload = new TrafficApp.init();
