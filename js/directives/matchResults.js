app.directive('matchResults',function(){
    return {
	restrict : "E",
	templateUrl : "html/views/matchResults.html"
    }
}).directive('matchResultsTable',function(){
    return {
	restrict : "A",
	templateUrl : "html/views/matchResultsTable.html",
	scope : {
	    value : "="
	}
    }
});
