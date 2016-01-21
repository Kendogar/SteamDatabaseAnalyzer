/**
 * Created by Lars on 18.01.2016.
 */
$( document ).ready(function() {
    $.ajax({
        type: "POST",
        url: "gameListHandler.php",
        success: function(resultData) {
            $("tbody").append(resultData)
            $("#myTable").tablesorter();
            $("button.deleteButton").click(function()
            {
                var gameToDelete = $(this).attr('name');
                console.log("trying to delete");
                 $.ajax(
                 {
                 url : 'deleteHandler.php',
                 type: "POST",
                 data : { action: "delete", game: gameToDelete },
                 success:function(data) {
                     window.location.reload()
                    }
                 });
            });
            $("button.playedButton").click(function()
            {
                var gameToMark = $(this).attr('name');
                $.ajax(
                    {
                        url : 'playedHandler.php',
                        type: "POST",
                        data : { action: "mark", game: gameToMark },
                        success:function(data) {
                            window.location.reload()
                        }
                    });
            });
        }
    });

    $("#ajaxFilterForm").submit(function(e)
    {
        var postData = $(this).serializeArray();
        console.log(postData);
        var formURL = $(this).attr("action");
        $.ajax(
            {
                url : formURL,
                type: "POST",
                data : postData,
                success:function(data)
                {
                    console.log(data)
                    $("tbody").empty();
                    $("tbody").append(data);
                    $("#myTable").tablesorter();
                    $("button.deleteButton").click(function()
                    {
                        var gameToDelete = $(this).attr('name');
                        console.log("trying to delete");
                        $.ajax(
                            {
                                url : 'deleteHandler.php',
                                type: "POST",
                                data : { action: "delete", game: gameToDelete },
                                success:function(data) {
                                    console.log(data);
                                }
                            });
                    });
                    $("button.playedButton").click(function()
                    {
                        var gameToMark = $(this).attr('name');
                        $.ajax(
                            {
                                url : 'playedHandler.php',
                                type: "POST",
                                data : { action: "mark", game: gameToMark },
                                success:function(data) {
                                    window.location.reload()
                                }
                            });
                    });
                }
            });
        e.preventDefault(); //STOP default action
    });
});