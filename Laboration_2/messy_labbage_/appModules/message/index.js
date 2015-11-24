"use strict";

var debug = require("debug")("message");
var express = require("express");
var router = express.Router();
var mModel = require("./messageModel");

router.get("/", function(req, res) {
    debug("Called message");
    mModel.getMessages(function(err, result) {
      var view;
      if(req.session.userID !== undefined && req.session.role === "user") {
        view = "index";
      } else if (req.session.userID !== undefined && req.session.role === "admin") {
        view = "admin";
      } else {
        return res.redirect("/");
      }
      res.render(
        "./message/views/" +view,
        {
          title: "Messages!",
          layout: "default",
          result: result
        }
      );

    });
});

router.get("/data", function(req, res) {
    debug("Called message");
    mModel.getMessages(function(err, result) {
      res.send(result);
    });
});

router.post("/", function(req, res) {
    debug("Called message with POST");
    //debug(req.body.message);
    var message = req.body.message;
    mModel.addMessages(message, req.session.userID, function(err) {
      if(err) {
        res.sendStatus(400);
      }
      res.sendStatus(200);
    });
});

router.post("/delete", function(req, res) {
    debug("Called message to delete");
    var message = req.body.messageID;
    mModel.deleteMessages(message, function(err) {
      if(err) {
        res.sendStatus(400);
      }
      res.sendStatus(200);
    });

});
module.exports = router;
