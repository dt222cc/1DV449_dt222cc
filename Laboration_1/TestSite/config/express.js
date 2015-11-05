/**
 This is a global configuration file for the express framework
 */
"use strict";

var express = require("express");
var bodyParser = require("body-parser");
var morgan = require("morgan");             // log requests to the console (express4)
var compression = require("compression");
var path = require("path");
var config = require("./environment");
var exphbs = require("express-handlebars");
var debug = require("debug")("express");
var session = require('express-session');
var FileStore = require('session-file-store')(session);


module.exports = function(app) {
    var env = app.get("env");

    debug(__dirname + "/../websites/views");
    // Setting the view root - not recommended structure
    app.set("views", __dirname + "/../websites/views");


    // Configure the handlebar
    app.engine(".html", exphbs(
        {
            defaultLayout: "index",
            extname: ".html",
            layoutsDir:  __dirname + "/../websites/views/layouts",
            partialsDir: __dirname + "/../websites/views/partials"
        }));
    app.set("view engine", ".html");

    app.use(compression());
    app.use(bodyParser.urlencoded({ extended: false }));
    app.use(bodyParser.json());

    // config the session
    app.use(session({
        store: new FileStore(),
        secret: 'this is secret don´t tell anyone!',
        cookie: {
            httpOnly: true,
            secure: false,

        },
        resave: false,
        saveUninitialized: true
    }));



    if (env === "production") {
        app.use(express.static(path.join(config.root, "public")));
        app.set("appPath", config.root + "/public");
        app.use(morgan("tiny"));
    }

    if (env === "development" || env === "test") {
        app.use(morgan("dev"));
    }
};