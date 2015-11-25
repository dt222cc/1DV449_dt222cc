"use strict";

/**
 * Main application file
 */

var server;

// To use debug start server with "DEBUG=start node app.js"
// more info https://www.npmjs.com/package/debug
var debug = require("debug")("start");
var express = require("express");

// Set default node environment to development
process.env.NODE_ENV = process.env.NODE_ENV || "development";
debug("Enviroment mode is: ", process.env.NODE_ENV);

// read the  enviroment configuration
var config = require("./config/environment/");
var app = express();

// read express configuration
require("./config/express")(app);

// read routes
require("./routes")(app);

// sqlite3 init
// read routes
require("./config/sqlite3");

// the http server starts
server = app.listen(config.port, function() {
    var host = server.address().address;
    var port = server.address().port;
    console.log("Example app listening at http://%s:%s, ENV: %s", host, port, app.get("env"));
});
