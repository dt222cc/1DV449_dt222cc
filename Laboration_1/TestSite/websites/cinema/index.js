/**
 *
 */

"use strict";

var express = require("express");
var router = express.Router();
var debug = require("debug")("cinema");

router.get("/", function(req, res) {
    debug("Got connection");
    res.render("cinema/index", {title: "Welcome to cinema!"});
});
router.get("/check", function(req, res) {

    debug("Day: %s, Movie: %s", req.query.day, req.query.movie);
    res.send(checkAvability(req.query.day, req.query.movie));
  //  res.sendStatus(200);
});

module.exports = router;

var data = [
    {day: "01", movie: "01", status: 0, time: "16:00"},
    {day: "01", movie: "01", status: 1, time: "18:00"},
    {day: "01", movie: "01", status: 0, time: "21:00"},

    {day: "01", movie: "02", status: 1, time: "16:00"},
    {day: "01", movie: "02", status: 1, time: "18:00"},
    {day: "01", movie: "02", status: 0, time: "21:00"},

    {day: "01", movie: "03", status: 1, time: "16:00"},
    {day: "01", movie: "03", status: 0, time: "18:00"},
    {day: "01", movie: "03", status: 0, time: "21:00"},


    {day: "02", movie: "01", status: 1, time: "16:00"},
    {day: "02", movie: "01", status: 1, time: "18:00"},
    {day: "02", movie: "01", status: 1, time: "21:00"},

    {day: "02", movie: "02", status: 1, time: "16:00"},
    {day: "02", movie: "02", status: 1, time: "18:00"},
    {day: "02", movie: "02", status: 0, time: "21:00"},

    {day: "02", movie: "03", status: 1, time: "16:00"},
    {day: "02", movie: "03", status: 0, time: "18:00"},
    {day: "02", movie: "03", status: 0, time: "21:00"},


    {day: "03", movie: "01", status: 1, time: "16:00"},
    {day: "03", movie: "01", status: 1, time: "18:00"},
    {day: "03", movie: "01", status: 0, time: "21:00"},

    {day: "03", movie: "02", status: 1, time: "16:00"},
    {day: "03", movie: "02", status: 1, time: "18:00"},
    {day: "03", movie: "02", status: 0, time: "21:00"},

    {day: "03", movie: "03", status: 1, time: "16:00"},
    {day: "03", movie: "03", status: 0, time: "18:00"},
    {day: "03", movie: "03", status: 0, time: "21:00"}
];

function checkAvability(day, movie) {
    var arr = [];
    data.filter(function(current) {
        return current.day === day && current.movie === movie;
    }).forEach(function(current) {
        var obj = {status: current.status, time: current.time, movie: current.movie};
        arr.push(obj);
    });

    return JSON.stringify(arr);
}