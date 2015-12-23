var app = angular.module("plAdvisor",[]).controller('mainController',['$scope','$http','$location','$window','$sce','common',function($scope,$http,$location,$window,$sce,common){
    $scope.data      = {};
    $scope.selected  = "bio";
    $scope.matchPage = true;
    $scope.common    = common;

    // Methods
    $scope.getHTML = function(html){
	if ( html )
	    return $sce.trustAsHtml(html.replace(/description/i,""));
	return "";
    }
    $scope.verifyImage = function(string){
	if ( string.match(/\<img/) != null ){
	    var img = string.substr(string.search(/src\=[\"\']{1}/));
	    return img.substr(0,img.search(/[\"\']/));
	}
	else
	    return string;
    }
    // Init by grabbing advisor info
    $http({
	method: 'POST',
	url: "./php/get_info.php",
	data: $.param({'id':$window.advisorId,'type':'Advisor'}),
	headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).then(function(response){
	console.log(response);
	$scope.data = response.data;
	d3load($window.advisorId,response.data.weights);
    });
}]);
