app.directive("displayType",['$window',function($window){
    return {
	restrict:"E",
	templateUrl:"html/views/single_display_"+$window.pageType.toLowerCase()+".html"
    };
}]);
