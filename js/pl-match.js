String.prototype.replaceAll = function(find,replace){
    return this.replace(new RegExp(find,"g"),replace);
}
var app = angular.module("plMatch",[]).controller("MatchController",['$scope','$http','$window','$timeout','$sce',function($scope, $http, $window, $timeout, $sce){
    var resultPage     = 1;
    var savedResources = {};
    $scope.display = "advisors";

    $scope.university = "Harvard_University";
    $scope.results = {
	advisors : [],
	courses  : [],
	theses   : [],
	grants   : []
    };
    $scope.delims = {
	departments : []
    };
    $scope.departments = [];
    $scope.resultsLength = {};

    $scope.displayHTML = function displayHTML(html){
	return $sce.trustAsHtml(html);
    }
    $scope.toggle   = function(department){
	var attrName = department.replace(/\s/g,"_");
	if ( _.indexOf($scope.delims.departments,department) > -1 ){
	    $("[name='"+attrName+"']").show();
	}
	else {
	    $("[name='"+attrName+"']").hide();
	}
    }
    $scope.getEmail    = function getEmail(email){
	var json = $window.JSON.parse(email);
	if ( json == null )
	    return email;
	else
	    return json["0"];
    }
    $scope.search   = function search(){
	$http({
	    method: 'POST',
	    url: "./php/magic_match_test_page.php",
	    data: $.param({"input":$("#search_box").val(),"session":$window.session_id,"page":resultPage}),
	    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function(response){
	    console.log(response);
	    // THIS LINE IS FOR TESTING PURPOSES ONLY
	    if ( typeof response.data == "string" && response.data.search("Not logged in") > -1 ){
		alert("Please log in to test this page. This warning is for test purposes ONLY");
	    }
	    // Loop through and find departments
	    var temp = {out:[]};
	    var tempList = {};
	    for ( var type in response.data ){
		var data = response.data[type];
		
		for ( var i = 0, n = data.length; i < n; i++ ){
		    var deptName = data[i].department;
		    // Get the correct type name
		    switch (type){
			case "Advisor":{
			    var typeName = "advisors";
			    break;
			}
			case "Course":{
			    var typeName = "courses";
			    break;
			}
			case "Thesis":{
			    var typeName = "theses";
			    break;
			}
			case "Grant":{
			    var typeName = "grants";
			}
		    }
		    // Add to the resultsLength variable so we can display how many results in that department exist
		    if ( !tempList[typeName] )
			tempList[typeName] = {};
		    if ( !tempList[typeName][deptName] )
			tempList[typeName][deptName] = 1;
		    else
			tempList[typeName][deptName]++;

		    // Push the department to the temporary department list. It's temporary so that Angular doesn't try to
		    // update as it's building
		    if ( deptName !== null && temp[deptName] !== true ){
			temp.out.push(deptName);
			temp[deptName]=true;
			$scope.delims.departments.push(deptName);
		    }
		}
	    }
	    $scope.resultsLength = tempList;
	    $scope.departments = temp.out;
	    $scope.results.advisors = response.data.Advisor || [];
	    $scope.results.courses  = response.data.Course || [];
	    $scope.results.theses   = response.data.Thesis || [];
	    $scope.results.grants   = response.data.Grant || [];	    
	});
    };
    $scope.isSavedResource = function isSavedResource(id,type){
	if ( type == "advisors" )
	    type = "advisor";
	else if ( type == "courses" ){
	    type = "course";
	}
	else if ( type == "theses" ){
	    type = "thesis";
	}
	else if ( type == "grants" ){
	    type = "grant";
	}
	if ( savedResources[type] ){
	    return $window._.indexOf(savedResources[type],id) > -1;
	}
	return false;
    }
    $scope.showResource = function showResource(type){
	var resources;
	$scope.display = type;
	if ( (resources = $("#"+type+"_results")).length > 0 ){
	    $scope.display = type;
	}
    }
    $scope.toggleFavorite = function toggleFavorite(id,type){
	if ( type == "advisors" )
	    type = "advisor";
	else if ( type == "courses" ){
	    type = "course";
	}
	else if ( type == "theses" ){
	    type = "thesis";
	}
	else if ( type == "grants" ){
	    type = "grant";
	}
	if ( $scope.isSavedResource(id,type) ){
	    $http({
		method: 'POST',
		url: "./php/save_remove_resource.php",
		data: $.param({"id":id,"type":type,"saved":"Remove"}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    }).then(function(response){
		if ( response.data == "success" ){
		    savedResources[type] = $window._.reject(savedResources[type],function(testId){
			return testId == id;
		    });
		}
	    });
	}
	else {
	    $http({
		method: 'POST',
		url: "./php/save_remove_resource.php",
		data: $.param({"id":id,"type":type,"saved":"Save"}),
		headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	    }).then(function(response){
		console.log(response);
		if ( response.data == "success" ){
		    savedResources[type].push(id);
		}
	    });
	}	    
    }
    $scope.alphaExists = function alphaExists(desc){
	if ( desc != undefined && desc != null )
	    return (desc.match(/[a-z]/i)!=null);
	else
	    return false;
    }
    $scope.snippet = function snippet(desc){
	desc.replace(/^\s*Description\s*|/,"").replace(/|/g," ");
	if ( desc.length > 70 )
	    return desc.substring(0,67)+"...";
	else
	    return desc;
    }
    // Initializing code
    if ( $window.initQuery != undefined ){
	$(".search-bar").html(initQuery);
	$scope.search();
    }
    // Set the enter key to search
    $("#search_box").on("keydown",function(){
	var e = window.event;
	var u = e.charCode ? e.charCode : e.keyCode;
	if ( u == 13 ){
	    var form;
	    if ( (form = $("#test_drive")).length > 0 )
		form[0].submit();
	    else
		$scope.search();
	    e.preventDefault ? e.preventDefault() : null;
	    e.stopPropagation? e.stopPropagation() : e.cancelBubble();
	    return false;
	}
    });
    // Get the saved resources
    $http({
	method: 'POST',
	url: "./php/get_saved_resources.php",
    }).then(function(response){
	savedResources = response.data;
    });
}]).directive('autoGrow', function() {
    return function(scope, element, attr){
	var max       = attr["autoGrow"];
	var minHeight = element[0].offsetHeight;
	var lines     = 1;
	var maxLines  = -1; // Default means that the number of lines is unlimited
	var defHeight = element.height();
	paddingLeft   = element.css('paddingLeft'),
	paddingRight  = element.css('paddingRight');
	element.css("min-height",element.outerHeight());
	$("#search_button").css("max-height",element.css("min-height"));

	var $shadow = angular.element('<div id="auto_grow_shadow"></div>').css({
	    position: 'absolute',
	    top: -10000,
	    left: -10000,
	    width: element.width(),
	    fontSize: element.css('fontSize'),
	    fontFamily: element.css('fontFamily'),
	    lineHeight: element.css('lineHeight'),
	    resize:     'none',
	    "font-weight": element.css('fontWeight'),
	    "text-decoration":element.css('textDecoration'),
	    padding: element.css("padding"),
	    "word-wrap": "break-word",
	});
	// Check for a maximum height or max number of lines
	if ( max != "" ){
	    maxLines = Number(max);
	    element.css({
		"max-height":maxLines*element.outerHeight()+"px",
		"overflow-y":"auto"
	    });
	}
	angular.element(document.body).append($shadow);
 
	var update = function() {
	    var times = function(string, number) {
		for (var i = 0, r = ''; i < number; i++) {
		    r += string;
		}
		return r;
	    }
	    var val = element.val().replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/&/g, '&amp;')
		.replace(/\n$/, '<br/>&nbsp;')
		.replace(/\n/g, '<br/>')
		.replace(/\s{2,}/g, function(space) { return times('&nbsp;', space.length - 1) + ' ' });
	    $shadow.html(val);
	    
	    lines = $shadow.height()/parseInt(element.css("lineHeight"),10);
	    if ( lines <= maxLines ){
		element.css({
		    'height':Math.max($shadow.height(), minHeight) + 'px',
		    'overflow':'hidden'
		});
	    }
	    else {
		element.css("overflow-y","auto");
	    }
	}
	element.bind({
	    'blur':function(){
		element.scrollTop(0).css({
		    "height":defHeight,
		    "overflow":"hidden"
		});
	    },
	    'focus':function(){
		element.height($shadow.height());
		if ( lines >= maxLines )
		    element.css("overflow-y","auto");
	    },
	    'keyup keydown keypress change':update
	});
	update();
    }
}).directive('checkList', function() {
  return {
    scope: {
      list: '=checkList',
      value: '@'
    },
    link: function(scope, elem, attrs) {
	function handler(setup){
	    var checked = elem.prop('checked');
            var index = scope.list.indexOf(scope.value);
	    
            if (checked && index == -1) {
		if (setup) elem.prop('checked', false);
		else scope.list.push(scope.value);
            } else if (!checked && index != -1) {
		if (setup) elem.prop('checked', true);
		else scope.list.splice(index, 1);
            }
	}
	    
	var setupHandler = handler.bind(null, true);
	var changeHandler = handler.bind(null, false);
        
	elem.on('change', function() {
            scope.$apply(changeHandler);
	});
	scope.$watch('list', setupHandler, true);
    }
  };
});
