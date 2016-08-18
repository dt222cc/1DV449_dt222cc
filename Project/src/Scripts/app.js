(function() {
    'use strict';

    var app = {
        formInput: {
            forecastTime: undefined,
            locationOne: undefined,
            locationTwo: undefined
        },

        init: function() {
            app.setCurrentDateTime();
            app.initOfflineChecker();
            app.refreshLocalStorage();
            app.initForecastService();

            app.getFormInput();
            app.submitButtonHandler();

            // Update submit button if any the input fields change
            $('#O, #Z').on('change', function() {
                app.submitButtonHandler();
            });
        },

        setCurrentDateTime: function() {
            var date = new Date();

            // Get current date, pre fill and the minimum date
            var day   = date.getDate();
            var month = date.getMonth() + 1;
            var year  = date.getFullYear();
            if (month < 10) month = '0' + month;
            if (day < 10)   day   = '0' + day;

            // Add 5 days, to set the max date
            date.setDate(day + 4);
            var day2   = date.getDate();
            var month2 = date.getMonth() + 1;
            var year2  = date.getFullYear();
            if (month2 < 10) month2 = '0' + month2;
            if (day2 < 10)   day2   = '0' + day2;

            var today   = year  + '-' + month  + '-' + day;
            var maxDate = year2 + '-' + month2 + '-' + day2; // maxDate: limitation of forecasts

            $('#theDate').attr('value', today);
            $('#theDate').attr('min', today);
            $('#theDate').attr('max', maxDate);
        },

        initOfflineChecker: function() {
            window.addEventListener('offline', function(e) {
                console.log('You are offline!');
                $('#offline-div').show();
            });

            window.addEventListener('online', function(e) {
                console.log('Back online!');
                $('#offline-div').hide();
            });
        },

        refreshLocalStorage: function() {
            var locationsDiv;
            var forecastsDiv;
            var currentLocations;
            var currentForecasts;
            var newLocations;
            var newForecasts;
            var updatedLocations;
            var updatedForecasts;

            if (window.localStorage) {
                locationsDiv = $('#temp-locations');
                forecastsDiv = $('#temp-forecasts');

                // Get the current locations and forecasts from the localStorage
                currentLocations = localStorage.getItem('storedLocations') ? localStorage.getItem('storedLocations') : '[]';
                currentForecasts = localStorage.getItem('storedForecasts') ? localStorage.getItem('storedForecasts') : '[]';

                // Do stuff if the divs containing the data exists (outputted by ths server PHP code)
                if (locationsDiv.length === 1 && forecastsDiv.length === 1) {
                    newLocations = locationsDiv.text();
                    newForecasts = forecastsDiv.text();

                    // Remove the divs from the page when we have the values
                    locationsDiv.remove();
                    forecastsDiv.remove();

                    // Update localStorage for locations if there is new locations to be added
                    if (newLocations !== null && newLocations !== undefined && newLocations !== 'damnåäö') {
                        currentLocations = JSON.parse(currentLocations);
                        newLocations     = JSON.parse(newLocations);
                        updatedLocations = currentLocations;

                        if (currentLocations && newLocations) {
                            newLocations.forEach(function(newLoc) {
                                var found = currentLocations.some(function(el) {
                                    return el.toponymName === newLoc.toponymName || el.name === newLoc.name;
                                });

                                if (!found) {
                                    console.log('new location added: ' + JSON.stringify(newLoc));
                                    updatedLocations.push(newLoc);
                                }
                            });

                            localStorage.setItem('storedLocations', JSON.stringify(updatedLocations));
                            console.log('Refreshed the localStorage for locations.');
                        }
                    }

                    // Update localStorage for forecasts if there is new forecasts to be added
                    if (newForecasts !== null && newForecasts !== undefined) {
                        currentForecasts = JSON.parse(currentForecasts);
                        newForecasts     = JSON.parse(newForecasts);
                        updatedForecasts = currentForecasts;

                        if (currentForecasts && newForecasts) {
                            newForecasts.forEach(function(newForcast) {
                                var found = currentForecasts.some(function(el) {
                                    return el.locationName === newForcast.locationName &&
                                        el.forecastTime === newForcast.forecastTime;
                                });

                                if (!found) {
                                    console.log('new forecast added: ' + JSON.stringify(newForcast));
                                    updatedForecasts.push(newForcast);
                                }
                            });

                            localStorage.setItem('storedForecasts', JSON.stringify(updatedForecasts));
                            console.log('Refreshed the localStorage for forecasts.');
                        }
                    }
                } else {
                    console.log('Skipping the process of refreshing cache.');
                    // Perhaps add functionality to trim the current forecasts from the cache.
                }
            } else {
                // = limited functionality: must be online as localstorage is needed for offline
                //   also introduced more calls to the API services by having no localstorage.
                console.log('This browser has no local storage support.');
            }
        },

        // If 'online' and missing a location or any forecast: do the server script.
        // If 'offline': prevent form submission and get/display available forecasts on the locations.
        // Further work on this function would be refactoring.
        initForecastService: function() {
            var locationsLocal;
            var forecastsLocal;
            var noForecastMessage = 'You are offline and both of the forecasts could not be ' +
                                    'obtained from the stored results in your browser. ' +
                                    'Please try again later when you are online again.';

            $('form').submit(function(e) {
                app.getFormInput();

                // Prevent form submission if offline or if the form is not complete
                if (!navigator.onLine /*|| !app.formInput.locationOne || !app.formInput.locationTwo*/) {
                    e.preventDefault();
                }

                if (window.localStorage === undefined) {
                    if (!navigator.onLine) {
                        alert('You are offline and this browser does not have localStorage support, ' +
                            'you need to be online to be able to use this application on your current browser.');
                    }
                } else {
                    locationsLocal = localStorage.getItem('storedLocations');
                    forecastsLocal = localStorage.getItem('storedForecasts');

                    // Check if we have forecasts in the cache
                    if (!app.forecastsExists(locationsLocal, forecastsLocal)) {
                        if (navigator.onLine) { // No cache and is online: server script
                            app.getForecastFromServer(locationsLocal, forecastsLocal);
                        } else { // No cache and is offline
                            console.log(noForecastMessage);
                            alert(noForecastMessage);
                        }
                    } else { // If forecasts 'could' exist in the storage: start with locations then forecasts
                        app.getLocationsFromLocalStorage(JSON.parse(locationsLocal), function(locationOne, locationTwo) {
                            // If ONLINE and MISSING a location, do server script to get the location/s and forecasts
                            if (navigator.onLine && (locationOne === undefined || locationTwo === undefined)) {
                                app.getForecastFromServer(locationsLocal, forecastsLocal);
                            }
                            // If ONLINE and BOTH locations was available or OFFLINE.
                            else {
                                app.getForecastsFromLocalStorage(JSON.parse(forecastsLocal), locationOne, locationTwo,
                                    function(forecastOne, forecastTwo) {
                                        if (navigator.onLine) { // Get forecasts from server because we are ONLINE
                                            if (forecastOne === undefined || forecastTwo === undefined) {
                                                app.getForecastFromServer(locationsLocal, forecastsLocal);
                                            } else {
                                                console.log('Is online and both forecasts was found in the cache. Preventing form submission.');
                                                e.preventDefault();
                                                app.renderForecasts(forecastOne, forecastTwo, locationOne, locationTwo);
                                            }
                                        } else { // Offline: Render available forecasts
                                            if (forecastOne === undefined && forecastTwo === undefined) {
                                                console.log(noForecastMessage);
                                                alert(noForecastMessage);
                                            } else {
                                                app.renderForecasts(forecastOne, forecastTwo, locationOne, locationTwo);
                                            }
                                        }
                                });
                            }
                        });
                    }
                }
            });
        },

        getLocationsFromLocalStorage: function(locations, callback) {
            var locationOne;
            var locationTwo;

            if (locations) {
                locations.forEach(function(location) {
                    if (app.formInput.locationOne.toLowerCase() === location.name.toLowerCase()) {
                        locationOne = location;
                    }
                    if (app.formInput.locationTwo.toLowerCase() === location.name.toLowerCase()) {
                        locationTwo = location;
                    }
                });
            }

            callback(locationOne, locationTwo);
        },

        getForecastsFromLocalStorage: function(forecasts, locationOne, locationTwo, callback) {
            var forecastOne;
            var forecastTwo;

            if (locationOne && locationTwo) {
                forecasts.forEach(function(forecast) {
                    if (forecast.forecastTime === app.formInput.forecastTime) {
                        if (forecast.locationName === locationOne.name) {
                            forecastOne = forecast;
                        }
                        if (forecast.locationName === locationTwo.name) {
                            forecastTwo = forecast;
                        }
                    }
                });
                callback(forecastOne, forecastTwo);
            } else {
                callback(forecastOne, forecastTwo);
            }
        },

        /* Initiates the server script */
        getForecastFromServer: function(locationsLocal, forecastsLocal) {
            console.log('Getting forecasts from the server because user/browser is online ' +
                'and we are missing one or both of the locations or forecasts.');

            // Submit form through the ajaxHandler (to pass the data from the cache to server)
            // With the addition of offline support, perhaps skip this middlepart.
            $.ajax({
                url: "ajaxHandler.php",
                type: "POST",
                data: {
                    locations: locationsLocal,
                    forecasts: forecastsLocal
                },
                success: function() { console.log("init server script"); },
                error: function ()  { console.log("error, ajax"); }
            });
        },

        /* Returns a boolean depending on if the forecasts from the localstorage can be parsed as/from JSON */
        forecastsExists: function(locations, forecasts) {
            if (locations !== undefined && forecasts !== undefined) {
                forecasts = JSON.parse(forecasts);
                locations = JSON.parse(locations);

                if (forecasts !== undefined && locations !== undefined) {
                    return true;
                }
            }

            return false;
        },

        // Get the form inputs and put them in an object for access
        getFormInput: function() {
            var timeStr;
            var date = new Date(Date.parse(document.getElementById('theDate').value));
            var time = parseInt(document.getElementById('hour').value + document.getElementById('minute').value);

            // Depending on the time, convert to any of 00, 03, 06, 09, 12, 15, 18, 21
            // (because of three hour forecasts from the API)
            if (time >= 2230)  {
                timeStr = ' 00:00:00';
                date = new Date(Date.parse(document.getElementById('theDate').value) + 86400000);
            }
            else if (time >= 1930)  { timeStr = ' 21:00:00'; }
            else if (time >= 1630)  { timeStr = ' 18:00:00'; }
            else if (time >= 1330)  { timeStr = ' 15:00:00'; }
            else if (time >= 1030)  { timeStr = ' 12:00:00'; }
            else if (time >= 730)   { timeStr = ' 09:00:00'; }
            else if (time >= 430)   { timeStr = ' 06:00:00'; }
            else if (time >= 130)   { timeStr = ' 03:00:00'; }
            else                    { timeStr = ' 00:00:00'; }

            app.formInput.forecastTime = date.toLocaleDateString() + timeStr;
             // WIP, part of fix for utf8
            app.formInput.locationOne  = document.getElementById('O').value.toLowerCase()
                .replace(/å/g, 'a').replace(/ä/g, 'a').replace(/ö/g, 'o');
            app.formInput.locationTwo  = document.getElementById('Z').value.toLowerCase()
                .replace(/å/g, 'a').replace(/ä/g, 'a').replace(/ö/g, 'o');

            document.weatherForm.O.value = app.formInput.locationOne;
            document.weatherForm.Z.value = app.formInput.locationTwo;
        },

        /* Update the submit button to be enabled or disabled */
        submitButtonHandler: function() {
            if ($('#O').val().length > 0 && $('#Z').val().length > 0 ) {
                $('input[type=submit]').prop('disabled', false);
            } else {
                $('input[type=submit]').prop('disabled', true);
            }
        },

        /* Params: Two forecast objects and two location objects */
        renderForecasts: function(forecastOne, forecastTwo, locationOne, locationTwo) {
            var myDiv = $('#forecasts-container');
            // Add the div container that the forcasts should recide in,
            // if it does not exists or empty it
            if (myDiv.length === 0) {
                $("<div id=\"forecasts-container\"></div>").insertAfter('#offline-div');
                myDiv = $('#forecasts-container');
            } else {
                myDiv.html('');
            }

            // Render the resulted forecasts
            if (forecastOne) { app.renderForecast(locationOne, forecastOne); }
            if (forecastTwo) { app.renderForecast(locationTwo, forecastTwo); }
        },

        /* Build <html> for a single forecast with data from the location and forecast objects */
        renderForecast: function(location, forecast) {
            // Parent div for the forecasts
            var forecastsDiv = document.getElementById('forecasts-container');

            var forecastDiv = document.createElement('div');
            forecastDiv.className = 'forecast';

            // Location name <h3> with coordinates(lat/lng)
            var locationName = document.createElement('h3');
            locationName.innerHTML = location.toponymName;
            var coordinates = document.createElement('small');
            coordinates.innerHTML = '(' + location.lat + ', ' + location.lng + ')';
            locationName.appendChild(coordinates);
            forecastDiv.appendChild(locationName);

            // DateTime
            var dateTime = document.createElement('p');
            dateTime.innerHTML = forecast.forecastTime;
            forecastDiv.appendChild(dateTime);

            // Symbol/icon that describes the forecast
            var weatherSymbol = document.createElement('div');
            weatherSymbol.className = 'weather-symbol';
            var img = document.createElement('IMG');
            img.src = 'Content/images/' + forecast.icon + '.png';
            img.alt = 'weather description image';
            img.className = 'img-thumbnail img-responsive';
            img.width = '100';
            weatherSymbol.appendChild(img);
            forecastDiv.appendChild(weatherSymbol);

            // Short forecast description (in english from the API)
            var description = document.createElement('div');
            description.innerHTML = forecast.description;
            forecastDiv.appendChild(description);

            forecastDiv.appendChild(document.createElement('br')); // Optional

            var temperature = document.createElement('div');
            temperature.className = 'weather-temperature';
            temperature.innerHTML = forecast.temperature.toString() + ' &#8451';
            forecastDiv.appendChild(temperature);

            forecastsDiv.appendChild(forecastDiv);
        }
    };

    window.onload = new app.init();
})();
