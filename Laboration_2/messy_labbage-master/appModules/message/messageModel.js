"use strict";
var debug = require("debug")("messageModel");

var sqlite3 = require("sqlite3").verbose();
console.log("appModules/siteViews/static/message.db");
var db = new sqlite3.Database("appModules/siteViews/static/message.db");

function getMessages(callback) {
  var sql = "SELECT username, message, created, message.id FROM message INNER JOIN user ON message.userID = user.id ORDER BY message.id DESC";
  debug(sql);
  db.all(sql, [], function(err, rows) {
    if(err) {
      callback(err);
    } else {
      debug(rows);
      callback(null, rows);
    }
  });
}

function addMessages(message, userID, callback) {
    debug(message +":" +userID);
    db.run("INSERT INTO message (message, userID) VALUES (?, ?)", [message, userID], function(err, res) {
      if(err) {
        debug(err);
        return callback(err);
      }
      debug("res: ", res);
      callback(null, res);
    });
}

function deleteMessages(messageID, callback) {
  db.run("DELETE FROM message WHERE id = ?", [messageID], function(err, res) {
    debug(err);
    if(err) {
      return callback(err);
    }
    debug("deleted with: " +messageID);
    callback(null, res);
  });
}

module.exports = {
  getMessages : getMessages,
  addMessages : addMessages,
  deleteMessages : deleteMessages
};
