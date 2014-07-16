app.directive('autoGrow', function() {
    return function(scope, element, attr){
	var options   = {
	    hide : true, // Shrinks the textarea to default height when focus is removed from the textarea
	    maxLines : -1,
	}
	var minHeight = element[0].offsetHeight;
	var lines     = 1;
	var maxLines  = -1; // Default means that the number of lines is unlimited
	var defHeight = element.height();

	// Get any options
	var ops = attr["autoGrow"].split(";");
	// Process the options
	for ( var i = ops.length-1; i > -1; i-- ){
	    var cmd = ops[i].split(":");
	    if ( options[cmd[0]] )
		options[cmd[0]] = cmd[1];
	}
	paddingLeft   = element.css('paddingLeft'),
	paddingRight  = element.css('paddingRight');
	element.css("min-height",element.outerHeight());
	$("#search_button,.search-button").css({
	    "max-height":element.css("min-height"),
	    "font-size":element.css("min-height").replace("px","")*1-10+"px"
	});

	var $shadow = angular.element('<div id="auto_grow_shadow"></div>').css({
	    position: 'absolute',
	    top: -10000,
	    left: -10000,
	    width: element.width(),
	    fontSize: element.css('fontSize'),
	    fontFamily: element.css('fontFamily'),
	    lineHeight: element.css('lineHeight'),
	    resize:     'none',
	    "font-weight": element.css('fontWeight'),
	    "text-decoration":element.css('textDecoration'),
	    padding: element.css("padding"),
	    "word-wrap": "break-word",
	});
	// Check for a maximum height or max number of lines
	if ( options.maxLines != "" ){
	    maxLines = Number(options.maxLines);
	    element.css({
		"max-height":maxLines*element.outerHeight()+"px",
		"overflow-y":"auto"
	    });
	}
	angular.element(document.body).append($shadow);
 
	var update = function() {
	    var times = function(string, number) {
		for (var i = 0, r = ''; i < number; i++) {
		    r += string;
		}
		return r;
	    }
	    var val = element.val().replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/&/g, '&amp;')
		.replace(/\n$/, '<br/>&nbsp;')
		.replace(/\n/g, '<br/>')
		.replace(/\s{2,}/g, function(space) { return times('&nbsp;', space.length - 1) + ' ' });
	    $shadow.html(val);
	    
	    lines = $shadow.height()/parseInt(element.css("lineHeight"),10);
	    if ( lines <= maxLines ){
		element.css({
		    'height':Math.max($shadow.height(), minHeight) + 'px',
		    'overflow':'hidden'
		});
	    }
	    else {
		element.css("overflow-y","auto");
	    }
	}
	element.bind({
	    'blur':function(){
		if ( options.hide == true ){
		    element.scrollTop(0).css({
			"height":defHeight,
			"overflow":"hidden"
		    });
		}
	    },
	    'focus':function(){
		element.height($shadow.height());
		if ( lines >= maxLines )
		    element.css("overflow-y","auto");
	    },
	    'keyup keydown keypress change':update
	});
	update();
    }
});
