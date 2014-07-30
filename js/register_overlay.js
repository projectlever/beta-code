function showForm(form_id){
    $("[name='float-form']").hide();
    $("#"+form_id).show();
}
function closeForm(){
    if ( loggedIn == false && ( ignore == null || ignore == false ) )
	return;
    var e = window.event;
    if ( e.target )
	var target = e.target;
    else if ( e.srcElement ){
	var target = e.srcElement;
    }
    if (target.nodeType == 3) // defeat Safari bug
	target = target.parentNode;
    if ( target.className.search("container") > -1 || target.className.search("row") > -1 || target.className.search("layer") > -1 )
	$("[name='float-form']").hide();
}
