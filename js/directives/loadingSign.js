app.directive("loadingSign",function(){
    return {
	restrict:"E",
	template:"<div style='position:fixed;z-index:5;top:50px;left:0;background-color:#fff;width:100%;height:100%;display:block'><img src='http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif' class='loading-gif' style='display:block' /></div>",
	link : function(scope,elem,attrs){
	    scope.onPageLoad = function(){
		$(".loading-gif").parent().hide();
	    }
	}
    }
});
