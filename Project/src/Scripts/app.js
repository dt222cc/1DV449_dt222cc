'use strict';

var app = {
    init: function()
    {
        app.sendLocalStorageToServerEvent();
        app.refreshLocalStorage();
    },

    sendLocalStorageToServerEvent: function()
    {
        $('form').on('submit',function() {
            // Local storage support
            if (window.localStorage !== undefined) {
                // Get data from local storage, like locations and forecasts
                var locationsLocal = localStorage.getItem('storedLocations');
                var forecastsLocal = localStorage.getItem('storedForecasts');
                // Then pass them on to be parsed as session variables
                $.ajax({
                    url: "ajaxHandler.php",
                    type: "POST",
                    data: {
                        locations: locationsLocal,
                        forecasts: forecastsLocal
                    },
                    success: function() {
                        console.log("success, ajax");
                    },
                    error: function ()
                    {
                        console.log("error, ajax");
                    }
                });
            } else {
                alert("Storage Failed. Try refreshing");
            }
        });
    },

    refreshLocalStorage: function()
    {
        if (window.localStorage) {
            var locations = $('#temp-locations');
            var forecasts = $('#temp-forecasts');

            // Do stuff if the divs containing the data exists (outputted by PHP)
            if (locations.length == 1 && forecasts.length == 1) {
                console.log('Refreshing cache.');
                var newLocations = locations.text();
                var newForecasts = forecasts.text();

                // Remove the divs from the page when we have the values
                locations.remove();
                forecasts.remove();

                // Refresh when new and has not been broken by åäö
                var localLocations = localStorage.getItem('storedLocations');
                if (localLocations != newLocations && newLocations != null && newLocations != "damnåäö") {
                    console.log('Refreshed locations.');
                    localStorage.setItem('storedLocations', newLocations);
                }
                if (localStorage.getItem('storedForecasts') != newForecasts) {
                    console.log('Refreshed forecasts.');
                    localStorage.setItem('storedForecasts', newForecasts);
                }
            } else {
                console.log('Skip refreshing cache.');
            }
        } else {
            console.log("No local storage support on this browser.")
        }
    }
};

window.onload = new app.init();