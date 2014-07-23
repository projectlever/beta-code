// test
var diffTool = angular.module('diffTool',[]);
var currentFile = "";
diffTool.controller('diffToolListCtrl',['$scope',
					'$window',
					'$http',
					function($scope, $window, $http){
    var self = this;
    this.commits = [];
    this.files   = [];
    this.curCommit = 0;
    this.prevCommit = 1;
    this.fileDiff = "";
    $http.get("get_commit_log.php").success(function(data){
	self.commits = data;
    });
    this.detail = function detail(){
	var commit_id = $("#commit_id").text();
	$http({
	    method:"POST",
	    url:"commit_files.php",
	    data:"id="+commit_id,
	    headers:{'Content-Type': 'application/x-www-form-urlencoded'}
	}).success(function(data){
	    self.files = data;
	    $("#commit_files").show();
	});
    }
    this.commitDetails = function commitDetails($scope){
	if ( this.curCommit < 0 || this.prevCommit < 0 ){
	    alert("Commit numbers must be 0 or positive");
	    return;
	}
	this.curCommit = Math.round(this.curCommit);
	this.prevCommit = Math.round(this.prevCommit);
	if ( $window.event )
	    var fname = $(event_target($window.event)).text();
	if ( fname && 
	     ($window.currentFile == "" || $window.currentFile != fname))
	    $window.currentFile = fname;
	$http({
	    method:"POST",
	    url:"get_file_diff.php",
	    data:"file="+$window.currentFile+"&cur="+this.curCommit+"&prev="
		+this.prevCommit,
	    headers:{'Content-Type':'application/x-www-form-urlencoded'}
	}).success(function(data){
	    if ( data == "" )
		self.fileDiff = "No commit this far back.";
	    else
		self.fileDiff = data;
	});
    }
}]);
function event_target(e){
    var targ = e.target || e.srcElement;
    if (targ.nodeValue == 3)
	targ = targ.parentNode;
    return targ;
}
