app.factory('common',function(){
    return {
	alphaExists : function(string){
	    try {
		return string.match(/\S/) != null;
	    }
	    catch (e){
		return false;
	    }
	}
    }
});
