"use strict";
var MessageBoard = {

    messages: [],
    textField: null,
    messageArea: null,

    init:function()
    {
		    MessageBoard.textField = document.getElementById("inputText");
		    MessageBoard.nameField = document.getElementById("inputName");
        MessageBoard.messageArea = document.getElementById("messagearea");

        // Add eventhandlers
        document.getElementById("inputText").onfocus = function(e){ this.className = "focus"; }
        document.getElementById("inputText").onblur = function(e){ this.className = "blur" }
        document.getElementById("buttonSend").onclick = function(e) {MessageBoard.sendMessage(); return false;}
        //document.getElementById("buttonLogout").onclick = function(e) {MessageBoard.logout(); return false;}

        MessageBoard.textField.onkeypress = function(e){
                  if(!e) var e = window.event;

                  if(e.keyCode == 13 && !e.shiftKey){
                      MessageBoard.sendMessage();
                      return false;
                  }
              }
    },
    getMessages:function() {
        $.ajax({
    			type: "GET",
    			url: "/message/data"
    		}).done(function(data) { // called when the AJAX call is ready
          MessageBoard.messages = [];
          for(var i = 0; i < data.length; i+=1) {
  				      var obj = data[i];
                var text = obj.username +" said:\n" +obj.message;
                var mess = new Message(text, new Date(), obj.id);
                MessageBoard.messages.push(mess);
          }
  			  MessageBoard.renderMessages();
  			  document.getElementById("nrOfMessages").innerHTML = MessageBoard.messages.length;

        });

    },
    sendMessage:function(){
        if(MessageBoard.textField.value == "") return;
        // Make call to ajax
        $.ajax({
    			type: "POST",
    		  	url: "/message",
    		  	data: {message:MessageBoard.textField.value}
    		}).done(function(data) {
    		  //alert("Your message is saved! Reload the page for watching it");
          MessageBoard.getMessages();
    		});
    },
    renderMessages: function(){
        // Remove all messages
        MessageBoard.messageArea.innerHTML = "";
        // Renders all messages.
        for(var i=0; i < MessageBoard.messages.length; ++i){
            MessageBoard.renderMessage(i);
        }
        document.getElementById("nrOfMessages").innerHTML = MessageBoard.messages.length;
    },
    renderMessage: function(messageID){
        // Message div
        var div = document.createElement("div");
        div.className = "message";

        // Clock button
        var aTag = document.createElement("a");
        aTag.href="#";
        div.appendChild(aTag);

        // Message text
        var text = document.createElement("p");
        text.innerHTML = MessageBoard.messages[messageID].getHTMLText();
        div.appendChild(text);

        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "id");
        input.setAttribute("value", MessageBoard.messages[messageID].id);
        div.appendChild(input);

        // Time - Should fix on server!
        var spanDate = document.createElement("span");
        spanDate.appendChild(document.createTextNode(MessageBoard.messages[messageID].getDateText()))

        div.appendChild(spanDate);

        var spanClear = document.createElement("span");
        spanClear.className = "clear";

        div.appendChild(spanClear);

        MessageBoard.messageArea.appendChild(div);
    },
    removeMessage: function(messageID){
		    if(window.confirm("Vill du verkligen radera meddelandet?")){
            //alert("Should remove: " +messageID);
            $.ajax({
        			type: "POST",
        		  	url: "/message/delete",
        		  	data: {messageID: messageID}
        		}).done(function(data) {
        		  //alert("Your message is saved! Reload the page for watching it");
              MessageBoard.getMessages();
        		});

        }
    },
    showTime: function(messageID){

         var time = MessageBoard.messages[messageID].getDate();

         var showTime = "Created "+time.toLocaleDateString()+" at "+time.toLocaleTimeString();

         alert(showTime);
    },
    logout: function() {
        MessageBoard.addRemoveMarker();
    },
    addRemoveMarker: function() {
        var messages = document.querySelectorAll(".message");
        for(var i = 0; i < messages.length; i += 1) {
            // remove button
            var aTag = messages[i].querySelector("a");
            aTag.href="#";
            aTag.onclick = function(){
              // TODO - Get the id from hidden fieild
                var hiddenID = this.parentNode.querySelector("input[type=hidden]").value;
                console.log(hiddenID);
    			      MessageBoard.removeMessage(hiddenID);
    			      return false;
    		    };

            var imgDel = document.createElement("img");
            imgDel.src="/static/images/delete.png";
            imgDel.alt="Remove message";

            aTag.appendChild(imgDel);
          //  messages[i].appendChild(aTag);
        }
    }
}

window.onload = MessageBoard.init;
