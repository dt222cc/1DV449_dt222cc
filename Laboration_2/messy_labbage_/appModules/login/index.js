"use strict";

var debug = require("debug")("login");
var express = require("express");
var router = express.Router();

router.get("/", function(req, res) {
    res.render(
      "./login/views/index",
      {
        title: "Welcome to index!",
        layout: "default"
      }
    );
});

router.post("/login", function(req, res) {
  debug("Logging in");
  var email = req.body.email;
  var password = req.body.password;


  // check if user could login
  var login = require("./lib/login");
  login.checkLogin(email, password, function(err, result) {
    if(err) {
      return res.redirect("/");
    }

    if(result !== undefined) {

      req.session.userID = result.id;
      req.session.role = result.role;
      return res.redirect("/message");
    }
  //  req.session.loggedIn = 0;
    return res.redirect("/");
  });


  // redirect correct
});

router.get("/logout", function(req, res) {
    req.session.userID = 0;
    res.redirect("/");
});

router.get("/test", function(req, res) {

  console.log("SESSION.ID: %s", req.session.userID);
  console.log("SESSION.ID: %s", req.session.role);
});



module.exports = router;
