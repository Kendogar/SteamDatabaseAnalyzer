/**
 * Created by Lars on 18.01.2016.
 */
$( document ).ready(function() {

    $("#randomForm").submit(function(e)
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
                }
            });
        e.preventDefault(); //STOP default action
    });
});