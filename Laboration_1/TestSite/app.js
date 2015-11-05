"use strict";

/**
 * Main application file
 */

var server;
var debug = require("debug")("start");
var express = require("express");

// Set default node environment to development
process.env.NODE_ENV = process.env.NODE_ENV || "development";
debug("Enviroment mode is: ", process.env.NODE_ENV);

var config = require("./config/environment");
var app = express();

// read configuration and routes
require("./config/express")(app);
require("./websites/routes")(app);

server = app.listen(config.port, function() {
    var host = server.address().address;
    var port = server.address().port;
    console.log("Example app listening at http://%s:%s, ENV: %s", host, port, app.get("env"));
});

// Expose app
//exports = module.exports = server;
