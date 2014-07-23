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
    $scope.editMode = ($window.editMode == 1); // Did we enter in edit mode?
    $scope.departments = [];
    $scope.schools = [];
    $scope.selectedSchool = "";
    $scope.selectedDepartment = "";

    $scope.common = common;
	 $scope.isSavedResource = function(){
		return true;	 
	 }
    $scope.setEditMode = function(bool){
	$scope.editMode = bool;
    }
    $scope.show = function(school){
	return true;
    }
    $scope.validate = function(){
	var name = $("#user_name").val();
	var dept = $("#department_select option:selected").text();
	var sch  = $("#school_select option:selected").text();
	if ( name == "" ){
	    alert("Please enter your name");
	    $("#user_name").focus();
	    return;
	}
	if ( dept.search("Select Department") > -1 ){
	    alert("Please select your department");
	    $("#department_select").focus();
	    return;
	}
	if ( sch.search("Select School") > -1 ){
	    alert("Please select your school");
	    $("#school_select").focus();
	    return;
	}
	// If all is good, send the form!
	$("#save_form").submit();
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
    $scope.schoolIsSelected = function(school){
    	console.log([school,$scope.selectedSchool]);
    	return school[0] == $scope.selectedSchool;
    }
    $scope.isSelected = function(name,type,user){
    	if ( user == undefined )
    		return "";
		if ( user.search(name) > -1 ){
			$("#"+name.replace(/\s/g,"_")).attr("selected",true);
		}
		if ( type == 'school' ){
			$scope.selectedSchool = name;		
		}
		else if ( type == 'department' ){
			$scope.selectedDepartment = name;		
		}
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
