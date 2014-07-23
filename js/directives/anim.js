app.directive(['anim',function(){
    return {
	restrict:"A",
	transclude:true,
	compile:function(scope,element,attr){
	    var fun = attr["anim"].split(":");
	    var time = fun[1];
	    fun = fun[0];
	    element.css({
		"-webkit-transition":"opacity "+time+"s ease-in",
		"-moz-transition":"opacity "+time+"s ease-in",
		"-ms-transition":"opacity "+time+"s ease-in",
		"-o-transition":"opacity "+time+"s ease-in",
		"transition":"opacity "+time+"s ease-in",
		"opacity":1
	    });	    
	    if ( fun == "pulse-fade" ){
		element.opacity = 1;
		element.timer = null;
		element.time = time;
		element.pulseFadeStart = function(){
		    this.timer = setInterval(function(){
			if ( this.opacity = 1 ){
			    element.css("opacity",0);
			    element.opacity = 0;
			}
			else {
			    element.css("opacity",1);
			    element.opacity = 1;
			}
		    },this.time*1000);
		}
		element.pulseFadeStop = function(){
		    clearTimeout(this.timer);
		    this.timer = null;
		}
	    }
	}
    }
}])
