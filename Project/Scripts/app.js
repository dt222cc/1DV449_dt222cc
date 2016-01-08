'use strict';

var app = {
    init: function()
    {
        $("#submit").click(function() {
            if (window.localStorage !== undefined) {
                var fields = "FAKE LOCALSTORAGE";
                console.log(fields);

                alert("Now Passing stored data to Server through AJAX jQuery");
                var data2 = $(this).serialize();
                console.log(data2);
                
                $.ajax({
                    type: "POST",
                    url: "index.php",
                    data: {data: fields},
                    success: function (data){
                        // alert(data);
                    },
                });
            } else {
                alert("Storage Failed. Try refreshing");
            }
        });
    },
};

window.onload = new app.init();