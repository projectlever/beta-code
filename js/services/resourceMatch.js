app.factory('resourceMatch',function(){
	return {
		match : function($scope,$http,id,type){
			$http({
				method: 'POST',
				url: "./php/get_similar.php",
				data: $.param({'id':id,'type':type}),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    		}).then(function(response){
		    if ( $scope.foundSimilarResources )
			$scope.foundSimilarResources(response.data);
		    else
			$scope.results.advisors = response.data.Advisor;
		    $("#results_container").show().css({"margin":"0","top":"0"});
		    $("#results_container .col-xs-2").remove();
		    $("#results_container .col-xs-7").removeClass("col-xs-7").addClass("col-xs-10");
		    $scope.sourceLoaded();
		    $("#network_loading_sign").parent().remove();
    		});
		}
	}
});
