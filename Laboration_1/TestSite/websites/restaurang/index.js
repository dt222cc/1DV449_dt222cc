/**
 *
 */

"use strict";

var express = require("express");
var router = express.Router();
var debug = require("debug")("restaurang");

// dinner/login
router.get("/", function(req, res) {
    debug("Got GET to dinner/login");
    res.send("You don´t GET it!");
});

router.post("/", function(req, res) {
    debug("Got POST to dinner/login");
    var username = req.body.username;
    var password = req.body.password;
    var tableValue = req.body.group1;

    if (username !== "zeke" || password !== "coys") {
        return res.sendStatus(403);
    }

    if (tableValue === undefined || tableValue.length === 0) {
        //res.write("{'error': 'bad arguments'}");
        return res.sendStatus(400);
    }

    return res.send("Tack " + username + " din bokning är OK!");
});

module.exports = router;