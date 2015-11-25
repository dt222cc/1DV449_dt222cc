"use strict";
var sqlite3 = require("sqlite3").verbose();
var db = new sqlite3.Database("appModules/siteViews/static/message.db");

function checkLogin(username, password, callback) {
  // check against db
  var sqlString = "SELECT * FROM user WHERE email = '" + username +"' AND password = '" + password +"'";
  db.get(sqlString, [], function(err, row) {
    if(err) {
      return callback(err);
    }
    if(row === undefined) {
      return callback(null, false);
    }
    // set session and return true
    return callback(null, row);

  });

}

module.exports.checkLogin = checkLogin;
