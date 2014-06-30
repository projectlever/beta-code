angular.module("plMatch",[]).controller("MatchController",['$scope','$http','$window',function($scope, $http, $window){
    $scope.display = "advisors";

    $scope.university = "Harvard_University";
    $scope.results = {
	advisors : [],
	courses  : [],
	theses   : [],
	grants   : []
    };
    $scope.departments = [];
    $scope.schools = [];
    $scope.add = function add(type,name){
	$scope[type].push(name);
	return name.replace(/\s/g,"_");
    }
    $scope.search   = function search(){
	$http({
	    method: 'POST',
	    url: "/advisors/magic_match_test_page.php",
	    data: $.param({"input":$("#search_box").val(),"session":$window.session_id}),
	    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	}).then(function(response){
	    console.log(response);
	    $scope.results.advisors = response.data.Advisor;
	    $scope.results.courses  = response.data.Course;
	    $scope.results.theses   = response.data.Thesis;
	    $scope.results.grants   = response.data.Grant;
	});
    };
    $scope.showResource = function showResource(type){
	var resources;
	if ( (resources = $("#"+type+"_results")).length > 0 ){
	    $scope.display = type;
	}
    }
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
}).filter("selectedSchools",function($filter){
    return function(schoolNames){
	var i, len;

	var checked = $filte('filter')(schoolNames,{checked:true});
	console.log(checked);

	return [];
    }
});
