/**
 This is a global configuration file for the express framework
 */
"use strict";

var express = require("express");
var bodyParser = require("body-parser");
var morgan = require("morgan");             // log requests to the console (express4)
var debug = require("debug")("express");
//var compression = require("compression");
var path = require("path");
var config = require("./environment");
var exphbs = require("express-handlebars");

var session = require('express-session');
var FileStore = require('session-file-store')(session);


module.exports = function(app) {
    var env = app.get("env");

    // Setting the view root - will use multiple views
    app.set("views", __dirname + "/../appModules");
    debug(__dirname + "/../appModules/siteViews/static/");
    // add static contents
    app.use("/static", head, express.static(__dirname + "/../appModules/siteViews/static/"));


    // Configure the handlebar
    app.engine(".html", exphbs(
    {
      //  defaultLayout: "index",
        extname: ".html",
        layoutsDir:  __dirname + "/../appModules/siteViews/layouts",
        partialsDir: __dirname + "/../appModules/siteViews/partials"
    }));
    app.set("view engine", ".html");

    app.use(bodyParser.urlencoded({ extended: false }));
    app.use(bodyParser.json());
    app.use(head);

    // config the session
    app.use(session({
        store: new FileStore({
          retries: 5,
          path:  __dirname + "/sessions/"
        }),
        secret: 'keyboard cat',
        cookie: {
            httpOnly: false,
            secure: false,

        },
        resave: true,
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

function head(req, res, next) {
  res.header('Cache-Control', 'private, no-cache, no-store, must-revalidate');
  res.header('Expires', '-1');
  res.header('Pragma', 'no-cache');
  next();
}
