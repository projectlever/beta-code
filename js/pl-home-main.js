$(document).ready(function(){
    // Activate the menu
    pl.activateIconBar();
    
    // Styling
    $("#try_it_layer").css("height",$(window).height());
    // Activate the down arrow
    var current    = 1; // Current frame being viewed
    var height     = $(".pl-frame").height();
    var autoScroll = false;
    $(".down_arrow").click(function(){
	if ( autoScroll == false ){
	    current++;
	    autoScroll = true;
	    if ( pl.autoScrollTo({"selector":"#frame_"+current,"offset":-50,"finished":function(){
		autoScroll = false;
	    }}) == false ){
		current--;
		autoScroll = false;
	    }
	}
    });
    $(window).on({
	"scroll" : function(){
	    var top = $(window).scrollTop();
	    if ( autoScroll == false ){
		current = Math.ceil(top/height);
		if ( current == 0 )
		    current = 1;
	    }
	    else {
		$(window).stop();
	    }
	    // Hide the down arrow if the user is near the bottom
	    if ( top > $(document.body).prop('scrollHeight')*0.7 )
		$(".down_arrow").hide();
	    else
		$(".down_arrow").show();
	},
	"resize" : function(){
	    height = $(".pl-frame").height();
	    setTimeout(function(){
		current = Math.round($(window).scrollTop()/height);
		if ( current == 0 )
		    current = 1;
		else
		    $(".down_arrow").trigger("click");
	    },100);
	}
    });
    // This will put the user at the nearest section if they do not begin at the top of the page
    $(window).trigger("resize");
});
