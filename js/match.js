var win = $(win,document);
$(window,document).on("resize",function(){
    $("#results_container").css("height",win.height()-150+"px");
});
function toggle(result){
    var el = $(result);
    var openResult = null;
    var status = el.attr("name");

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
	$(result).removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down").parents(".result-header").next()
	    .removeClass('hide').addClass('show');
	el.attr("name","open");
    }
}
