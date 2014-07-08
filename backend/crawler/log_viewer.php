<?php
if ( isset($_GET["get-data"]) ):
  $files = glob("./logs/*.json");
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
     $http.get("log_viewer.php?get-data=1").then(function(data){
       $scope.errors = data.data.incomplete;
     });
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
  <table>
    <tr ng-repeat="error in errors">
      <td onclick="$(this.parentNode).toggleClass('true')">
	  Fixed? 
      </td>
      <td>Row #{{error.row}} in sheet {{error.sheet}}</td>
      <td>
	<div ng-click="console.log(error.message)" style="cursor:pointer">{{error.message}}</div>
      </td>
    </tr>
  </table>
</body>
</html>
<?php
endif;
?>
