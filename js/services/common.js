app.factory('common',function(){
    return {
	alphaExists : function(string){
	    try {
		return string.match(/\S/) != null;
	    }
	    catch (e){
		return false;
	    }
	},
	replaceAll : function(find,replace,string){
	    var reg = new RegExp(find,"g");
	    return string.replace(reg,replace);
	}
    }
});
