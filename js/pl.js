/*** General functions that should work throughout the website ***/

var pl = new (function($, window){
    // global vars
    var self = this;
    var scrollTimer = null;
    this.activateIconBar = function(){
	function showMenu(){
	    $(".navbar-collapse").toggle();
	}

	if ( $(window).width() < 768 ){
	    $(".navbar-brand").on("click tap touchend",showMenu);
	}
	$(window).resize(function(){
	    if ( $(window).width() < 768 ){
		$(".navbar-brand").on("click tap touchend",showMenu);
	    }
	});	
    }
    var isWindowAt = function(target){
	if ( typeof target == "number" )
	    var top = target;
	else if ( typeof target == "string" )
	    var top = $(target).position().top;
	else if ( target.position )
	    var top = target.position().top;
	else
	    return false;
	return ( $(window).scrollTop() <= target+1 && $(window).scrollTop() >= target-1 );
    }
    this.autoScrollTo = function(options){
	if ( typeof options.selector == "string" )
	    options.selector = $(options.selector);
	if ( options.selector.length == 0 )
	    return false;
	var time = options.time || 1000;
	var target = options.selector.position().top+(options.offset?options.offset:0);
	if ( target < 0 )
	    return false;
	$("html,body").animate({scrollTop:target},time);
	if ( options.finished ){
	    scrollTimer = setInterval(function(){
		if ( isWindowAt(target) || $(window).scrollTop() + $(window).height() == $(document).height() ){
		    options.finished($(window).scrollTop());
		    clearTimeout(scrollTimer);
		}
	    },100);
	}
	return true;
    }
    return this;
})(jQuery,window);