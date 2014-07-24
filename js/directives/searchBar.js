app.directive('searchBar', ['matchAlgorithm',
    function (match) {
        return {
            restrict: "E",
            templateUrl: "html/views/searchBar.html",
            compile: function (scope, elem, attrs, controller) {
                return {
                    post: function (scope, elem, attrs, controller) {
                    		$(".search-bar").on("keydown",function(e){
                    			if ( !e )
                    				e = window.event;
                    			var u = e.charCode ? e.charCode : e.keyCode;
                    			if ( u == 13 ){
                    				if ( scope.onSearchButtonClick )
                    					scope.onSearchButtonClick();
	                    			match.match(scope);
	                    			e.preventDefault ? e.preventDefault() : null;
                    				e.stopPropagation ? e.stopPropagation() : e.cancelBubble();
                    				return false;
	                    		}                    		
                    		});
                        $(".search-button")
                            .click(function () {
                                match.match(scope);
                                if (scope.onSearchButtonClick)
                                    scope.onSearchButtonClick();
                            })
                            .css({
                                "font-size": $(".search-bar").outerHeight() - 12 + "px",
                                /* 12 = 2px for both padding-top(bottom) + 1px for both border-top(bottom) + 1px for image border + 5px padding */
                            }).parent().css("width", $(".search-button").width());
                    }
                }
            }
        }
    }
]).factory("matchAlgorithm", ['$http',
    function ($http) {
        return {
            match: function ($scope) {
            	 var url = $scope.searchUrl || "./php/magic_match_test_page.php";
                $http({
                    method: 'POST',
                    url: url,
                    data: $.param({
                        "input": $("#search_box").val(),
                        "page": $scope.resultPage ? $scope.resultPage : 1,
                        "test_drive": $scope.testDrive ? $scope.testDrive : "false"
                    }),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                }).then(function (response) {
							if ( $scope.onResultObtained )
								$scope.onResultObtained(response);                
                });
            }
        }
    }
]);