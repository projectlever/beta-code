var testDrive = false;
var app = angular.module("plAdvisor",[]).controller('mainController',
['$scope','$http','$location','$window','$sce','common','$timeout','resourceMatch',
function($scope,$http,$location,$window,$sce,common,$timeout,resourceMatch){
    var savedResources = {};
    var loadedSources  = 0;
    var sourcesNeeded  = 2;
    $scope.pageType      = $window.pageType;
    $scope.vizDataExists = false;
    $scope.data          = {};
    $scope.selected      = "bio";
    $scope.matchPage     = true;
    $scope.common        = common;
    $scope.results       = {};
    $scope.singleDisplay = true;
    $scope.display       = "advisors";
    $scope.testDrive     = false;
    $scope.displayDelimiters = false;
    $scope.id = 0;

    var sourceLoaded = $scope.sourceLoaded = function(){
		loadedSources++;
		if ( loadedSources == sourcesNeeded ){
	    	$("#loading_sign").hide();
		}
    }

    // Methods
    $scope.select  = function(thing){
		$scope.selected = thing;
    }
    $scope.getHTML = $scope.displayHTML = function(html){
		if ( html )
	    	return $sce.trustAsHtml(html.replace(/description/i,""));
		return "";
    }
    $scope.stripTags = function(html){
		return String(html).replace(/<[^>]+>/gm, '');
    }
    $scope.getEmail = function(email){
		return email;
    }
    $scope.toggleFavorite = function toggleFavorite(id,type){
		if ( type == "advisors" || type == "Advisor" ) type="advisor";
		else if ( type == "courses" || type == "course" || type == "Course" ) { type = "course"; }
		else if ( type == "theses" || type == "thesis" || type == "Thesis" ) { type = "thesis"; }
		else if ( type == "grants" || type == "funding" || type == "grant" || type == "Grant" ){ type == "grant"; }
			if ( $scope.isSavedResource(id,type) ){
				$("#star_load").css("display","inline");
	    		$http({
					method: 'POST',
					url: "./php/save_remove_resource.php",
					data: $.param({"id":id,"type":type,"saved":"Remove"}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    		}).then(function(response){
	    			$("#star_load").css("display","none");
	    			console.log(savedResources);
	    			console.log([id,type]);
					if ( response.data == "success" ){
		    			savedResources[type.toLowerCase()] = $window._.reject(savedResources[type],function(testId){
							return testId == id;
		    			});
					}
	    		});
			}
			else {
				$("#star_load").css("display","inline");
	    		$http({
					method: 'POST',
					url: "./php/save_remove_resource.php",
					data: $.param({"id":id,"type":type,"saved":"Save"}),
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    		}).then(function(response){
	    			$("#star_load").css("display","none");
	    			console.log(savedResources);
	    			console.log([id,type]);
					if ( response.data == "success" ){
						if ( type == "advisors" ) type = "advisor";
						else if ( type == "courses" ) { type = "course"; }
						else if ( type == "theses" ) { type = "thesis"; }
						else if ( type == "grants" || type == "funding" ){ type == "funding"; }
		    			savedResources[type.toLowerCase()].push(String(id));
					}
	    		});
			}	    
    	}
    	$scope.isSavedResource = function isSavedResource(id,type){
			if ( type == "advisors" || type == "Advisor" )
	    		type = "advisor";
			else if ( type == "courses" || type == "Course" ){
	    		type = "course";
			}
			else if ( type == "theses" || type == "Thesis" ){
	    		type = "thesis";
			}
			else if ( type == "grants" || type == "Funding" ){
	    		type = "grant";
			}
			if ( savedResources[type] ){
	    		if ( typeof id != "string" )
					id += "";
	    		return $window._.indexOf(savedResources[type],id) > -1;
			}
			return false;
    	}
    	$scope.verifyImage = function(string){
			if ( string.match(/\<img/) != null ){
	    		var img = string.substr(string.search(/src\=[\"\']{1}/));
	    		return img.substr(0,img.search(/[\"\']/));
			}
			else
	    		return string;
    	}
    	$scope.toType = function(obj) {
			return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
    	}									
    	// Init by grabbing advisor info
    	$http({
			method: 'POST',
			url: "./php/get_info.php",
			data: $.param({'id':$window.advisorId,'type':$scope.pageType}),
			headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    	}).then(function(response){
			console.log(response);
			$scope.data = response.data;
			$scope.id = response.data[pageType][pageType+"_ID"];
			
			// Get similar resources
	   	resourceMatch.match($scope,$http,$scope.id,pageType);
			
			// Set up the visualization
			$scope.vizDataExists = response.data.vizDataExists;
			if ( $scope.vizDataExists == true ){
	    		// This loop is REQUIRED. Otherwise, d3 tries to access #viz before it loads....This waits for #viz to load before running d3load
	    		function testForElement(){
					if ( $("#viz").length > 0 ){
		    			d3load($window.advisorId,response.data.weights,700);
		    			sourceLoaded();
					}
					else {
		    			$timeout(testForElement,500);
					}
	    		}
	    		$timeout(testForElement,500);
			}
			else {
	    		sourceLoaded();
			}
    	});
    	// Get saved resources
    	$http({
			method: 'POST',
			url: "./php/get_saved_resources.php",
    	}).then(function(response){
			for (var prop in response.data){
				var type = prop.toLowerCase();
				if ( type == "advisors" ) type = "advisor";
				else if ( type == "courses" ) { type = "course"; }
				else if ( type == "theses" ) { type = "thesis"; }
				else if ( type == "grants" || type == "funding" ){ type == "grant"; }
				savedResources[type] = response.data[prop];			
			}
			sourceLoaded();
	   });
}]);
