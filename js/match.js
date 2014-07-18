var win = $(win,document);
function toggle(result){
    if ( testDrive == true ){
	showForm('reg_form');
	return;
    }
    var el = $(result);
    if ( el.prop("tagName") == "TD" )
	el = el.parent().find("[name='opener']").find("span");    
    var openResult = null;
    var status = el.attr("name");
    var retrievedData = el.attr("data");
    var id = el.attr("resource-id");
    var type = el.attr("resource-type");

    function showData(){
	if ( status == "open" ){
	    el.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right").parents(".result-header").next()
		.removeClass('show').addClass('hide');
	    el.attr("name","closed");
	}
	else {
	    // Close the open result
	    if ( (openResult = el.parents("tbody").find("[name='open']")).length > 0 ){
		toggle(openResult[0]);
	    }
	    el.removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down").parents(".result-header").next()
		.removeClass('hide').addClass('show');
	    el.attr("name","open");
	}
    }

    if ( retrievedData == "no" ){
	switch (type){
	    case "advisors":
	    var resource = "Advisor";
	    break;
	    case "courses":
	    var resource = "Course";
	    break;
	    case "theses":
	    var resource = "Thesis";
	    break;
	    case "grants":
	    var resource = "Grant";
	    break;
	}
	$.post("./php/getResourceBlock.php",{"resource":resource,"id":id}).done(function(data){
	    el.parents(".result-header").next().find("[name='description_box']").html(data);
	    showData();
	    el.attr("data","yes");
	});
    }
    else {
	showData();
    }
}
