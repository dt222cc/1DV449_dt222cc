"use strict";
var sqlite3 = require('sqlite3').verbose();
var db = new sqlite3.Database('./appModules/siteViews/static/message.db');

db.serialize(function() {

  db.get("SELECT name FROM sqlite_master WHERE type='table' AND name='user'",
  function(error, row) {
    if (row !== undefined) {
      console.log("table user exists. cleaning existing records");
      db.run("DELETE FROM user", function(error) {
        if (error) {
          console.log(error);
        }

      });
      // this is the question running
      db.run("INSERT INTO user (email, username, password, role) VALUES('user@user.se', 'user1', 'pass1user!', 'user')");
      db.run("INSERT INTO user (email, username, password, role)  VALUES('user2@user.se', 'user2', '!pass@user!', 'user')");
      db.run("INSERT INTO user (email, username, password, role)  VALUES('admin@user.se', 'admin', '!admin@40', 'admin')");
    }
    else {
      console.log("creating tables: user");
      db.run("CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT, email VARCHAR(255), password VARCHAR(255), username VARCHAR(255), role VARCHAR(255))");

    }
  });
  db.get("SELECT name FROM sqlite_master WHERE type='table' AND name='message'",
  function(error, row) {
    if (row !== undefined) {
      console.log("table message exists. cleaning existing records");
      db.run("DELETE FROM message", function(error) {
        if (error) {
          console.log(error);
        }
      });
    } else {
      console.log("creating tables: message");
      db.run("CREATE TABLE message (id INTEGER PRIMARY KEY AUTOINCREMENT, message TEXT, created DATE, userID INTEGER)");
    }
    db.close();
  });

});
