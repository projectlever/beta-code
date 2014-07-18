var app = angular.module('profile',[]).controller('ProfileController',['$scope','$window','$http','$sce','common',function($scope,$window,$http,$sce,common){
    var removeList = {
	advisors : [],
	courses  : [],
	theses   : [],
	grants   : [],
	length   : 0
    };
    // View controlling variables
    $scope.profilePage = true; // This is used to tell the navbar to show 'Logout' instead of 'Profile'
    $scope.user = {}; // User information
    $scope.selected = 'savedResources'; // Default selected tab
    $scope.matchTextEdited = false; // If the student's match text was edited
    $scope.displayDelimiters = false; // Show the delimiters?
    $scope.results = {}; // Saved results
    $scope.display = "advisors";
    $scope.testDrive = false;
    $scope.savesEdited = false;
    $scope.editMode = false;
    $scope.departments = [];
    $scope.schools = [];

    $scope.common = common;

    $scope.setEditMode = function(bool){
	$scope.editMode = bool;
    }
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
    $scope.displayHTML = function(html){
	return $sce.trustAsHtml(html);
    }
    $scope.showResource = function(resource){
	$scope.display = resource;
    }
    $scope.isOnRemoveList = function(id,type){
	return _.find(removeList[type],function(arr){return arr == id;}) != undefined;
    }
    $scope.removeFavorites = function(){
	$http({
	    method: 'POST',
	    url: "./php/save_remove_resource.php",
	    data: $.param({"list":JSON.stringify(removeList),"saved":"Remove"}),
	    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function(response){
	    console.log(response);
	    if ( response.data == "success" ){
		for ( var type in removeList ){
		    for ( var i = removeList[type].length-1; i > -1; i-- ){
			$("#"+type+"_"+removeList[type][i]).parent().remove();
		    }
		}
		removeList = [];
		$scope.savesEdited = false;
	    }
	});
    }
    $scope.toggleFavorite = function toggleFavorite(id,type){
	var index = -1;
	if ( (index = _.indexOf(removeList[type],id)) > -1 ){
	    // Remove it from the remove list	    
	    removeList[type].splice(index,1);
	    removeList.length--;
	}
	else {
	    // Add it to the remove list
	    removeList[type].push(id);
	    removeList.length++;
	}
	if ( removeList.length > 0 )
	    $scope.savesEdited = true;
	else
	    $scope.savesEdited = false;
    }
    // Get user information
    $http({
	method: "GET",
	url: "php/get_user_info.php"
    }).then(function(response){
	console.log(response);
	$scope.user    = response.data;
	$scope.results.advisors = response.data.saved.Advisor;
	$scope.results.courses  = response.data.saved.Course;
	$scope.results.theses   = response.data.saved.Thesis;
	$scope.results.grants   = response.data.saved.Funding;
    });
    $http({
	method: "GET",
	url: "php/get_unique.php"
    }).then(function(response){
	console.log(response);
	$scope.schools = response.data.schools;
	$scope.departments = response.data.departments;
    });
}]);
