'use strict';

var app = {
    init: function()
    {
        $('form').on('submit',function() {
            alert("submit");
            // Localstorage support
            if (window.localStorage !== undefined) {
                // Get data from localstorage, like locations and forecasts
                var locationsLocal = "Location dummy";
                var forecastsLocal = "Forecasts dummy";
                // Then pass them to be parsed as session variables
                $.ajax({
                    url: "ajaxHandler.php",
                    type: "POST",
                    data: {
                        locations: locationsLocal,
                        forecasts: forecastsLocal
                    },
                    success: function(data) {
                        alert("success, ajax");
                    },
                    error: function ()
                    {
                        alert("error, ajax");
                    }
                });
            } else {
                alert("Storage Failed. Try refreshing");
            }
        });
    },
};

window.onload = new app.init();