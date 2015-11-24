/**
 * Main application routes
 */
"use strict";

//var express = require("express");
//var debug = require("debug")("routes");

module.exports = function(app) {

    // set routes for calendar - This is just static content
    app.use("/", require("./appModules/login"));
    app.use("/message", require("./appModules/message"));
  //  app.use("/user", require("./user"));

};
