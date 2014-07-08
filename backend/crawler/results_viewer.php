<?php
if ( isset($_GET["get-data"]) ):
  $files = glob("./results/*.json");
  echo file_get_contents($files[count($files)-1]);
else:
?>
<!DOCTYPE html>
<head>
  <title></title>
  <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.18/angular.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script type="text/javascript">
   angular.module("logViewer",[]).controller("logController",['$scope','$http',function($scope,$http){
     $scope.data = [];
     $http.get("results_viewer.php?get-data=1").then(function(data){
       $scope.data = data.data;
       console.log($scope.data);
     });
     $scope.title = function title(result){
       return result.new_data.name ? result.new_data.name : result.title ? result.title : "";
     }
   }]);
  </script>
  <style type="text/css">
   tr:nth-child(odd){
     background-color:#ddd;
   }
   .true {
     text-decoration: line-through;
   }
  </style>
</head>
<body ng-app="logViewer" ng-controller="logController as controller">
  <ul>
    <li ng-repeat="result in data">
      <ul>
	<li onclick="$(this).next().toggle()">{{title(result)}} --> {{result.message}}</li>
	<ul style="display:none">
	  <li>Backup File: {{result.backup_file}}</li>
	  <li onclick="$(this).next().toggle()">Old Data</li>
	  <li style="display:none">
	    <ul ng-repeat="(key,value) in result.old_data">
	      <li onclick="$(this).next().toggle()">{{key}}</li>
	      <li style="display:none">{{value}}</li>
	    </ul>
	  </li>
	  <li onclick="$(this).next().toggle()">New Data</li>
	  <li style="display:none">
	    <ul ng-repeat="(key,value) in result.new_data">
	      <li onclick="$(this).next().toggle()">{{key}}</li>
	      <li style="display:none">{{value}}</li>
	    </ul>
	  </li>
	</ul>
      </ul>
    </li>
  </ul>
</body>
</html>
<?php
endif;
?>
