var app = angular.module('profile',[]).controller('ProfileController',['$scope','$window','$http','common',function($scope,$window,$http,common){
    $scope.profilePage = true;
    $scope.user = {};
    $scope.common = common;
    $scope.selected = 'savedResources';
    $scope.matchTextEdited = false;

    $scope.saveMatchText = function(){
	if ( $scope.matchTextEdited == false )
	    return;
	$http({
	    method : "POST",
	    url : "php/save_match_text.php",
	    data: $.param({"text":$(".search-bar").val(),"id":$scope.user.id}),
	    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function(response){
	    if ( response.data == "saved" ){
		$scope.matchTextEdited = false;
	    }
	});
    }
    // Get user information
    $http({
	method: "GET",
	url: "php/get_user_info.php"
    }).then(function(response){
	console.log(response);
	$scope.user = response.data;
    });
}]);
