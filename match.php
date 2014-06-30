<?php  
include("php/match-page.php");
?>
<!DOCTYPE html>
<head>
  <title>Match</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="keywords" content="mentorship, collaboration, academia, guide, thesis, writing, library, research">
  <meta name="rights" content="Project Lever LLC">
  <meta name="description" content="Match student's research interests with advisers, professors, grants, and courses.">
  
  <!-- Le Styles -->

  <link type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans|Raleway:400,300,500|Oxygen:400" rel="stylesheet">		
  <link type="text/css" href="css/normalize.css" rel="stylesheet"/>
  <link type="text/css" href="css/bootstrap/css/bootstrap.css" rel="stylesheet"/>
  <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/bootstrap_extend.css" rel="stylesheet"/>
  <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/style.css" rel="stylesheet"/>
  <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/flexslider.css" rel="stylesheet"/>    
  <link type="text/css" href="css/pl-main.css" rel="stylesheet"/>
  
  <!-- Le Scripts-->		
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.0-beta.13/angular.min.js"></script>
  <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>    
  <script type="text/javascript" src="js/general.js"></script>
  <script type="text/javascript" src="js/pl-match.js"></script>
  <script type="text/javascript" src="js/match.js"></script>
</head>
<body class="pl-body" ng-app="plMatch" ng-controller="MatchController as controller">
  <!-- NAVBAR -->
  <div class="navbar navbar-fixed-top alt pl-navbar" data-spy="affix" data-offset-top="1000">
    <div class="container full-width" style="padding-left:0">
      <div class="navbar-collapse collapse pl-logo">
	<ul class="nav navbar-nav">
	  <li>
	    <a>Project Lever</a>
	  </li>
	</ul>
      </div>
      <div class="navbar-header"> 
	<a href="javascript:void(0)" class="navbar-brand">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
	</a>
      </div>
      <div class="navbar-collapse collapse navbar-right" id="navbar">
        <ul class="nav navbar-nav">
          <li>
	    <a href="http://projectlever.com/about.html">About</a>
          </li>
          <li>
	    <a href="http://projectlever.com/magazine">Magazine</a>
          </li>
	  <li>
	    <a href="explore.php">Explore</a>
	  </li>
	  <li>
	    <a href="profile.php">Profile</a>
	  </li>
        </ul>
      </div>
    </div>
  </div>
  <!-- END NAVBAR, START BODY -->
  <div class="pl-content pl-zebra" style="height:100px;">
    <div class="container full-width full-height">
      <div class="row full-height">
	<div class="col-xs-10 col-xs-offset-1 full-height">
	  <table class="match-parent">
	    <tr>
	      <td valign="middle" style="vertical-align:middle">
		<textarea class="search-bar search-bar-td" id="search_box" placeholder="Tell us about your interests!" auto-grow="2"></textarea>		
	      </td>
	      <td>
		<span class="glyphicon glyphicon-search search-button" ng-click="search()"></span>
		<script type="text/javascript">
		   $(".search-button").css({
		     "font-size":$(".search-bar").outerHeight()-12+"px", /* 12 = 2px for both padding-top(bottom) + 1px for both border-top(bottom) + 1px for image border + 5px padding */
		   }).parent().css("width",$(".search-button").width());
		</script>
	      </td>
	    </tr>
	  </table>
	</div>
      </div>
    </div>
  </div>
  <!-- RESULTS SECTION -->
  <div class="pl-content pl-zebra" id="results_container">
    <div class="layer" id="results_layer">
      <div class="container full-width full-height">
	<div class="row full-height" style="padding-top:4em">
	  <div class="col-xs-2 col-xs-offset-1 full-height">
	    <ul id="results_counter">
	      <li id="advisors_results_count" class="selected-result-type" ng-click="showResource('advisors')" ng-class="{'selected-result-type':display=='advisors','result-type':display!='advisors'}">
		Advisors
		<span>{{results.advisors.length}}</span>
	      </li>
	      <li id="courses_results_count" class="result-type" ng-click="showResource('courses')" ng-class="{'selected-result-type':display=='courses','result-type':display!='courses'}">
		Courses
		<span>{{results.courses.length}}</span>
	      </li>
	      <li id="theses_results_count" class="result-type" ng-click="showResource('theses')" ng-class="{'selected-result-type':display=='theses','result-type':display!='theses'}">
		Theses
		<span>{{results.theses.length}}</span>
	      </li>
	      <li id="grants_results_count" class="result-type" ng-click="showResource('grants')" ng-class="{'selected-result-type':display=='grants','result-type':display!='grants'}">
		Grants
		<span>{{results.grants.length}}</span>
	      </li>
	    </ul>
	    <ul id="school_delims">
	      <li ng-repeat="school in schools">
		<input type="checkbox" name="school_names" id="delim_{{school}}" ng-checked="school.checked" ng-model="school.checked" /><label for="delim_{{school}}">{{school}}</label>
	      </li>
	    </ul>
	  </div>
	  <div class="col-xs-6 col-xs-offset-1 full-height" style="overflow:auto">
	    <table class="match-parent" style="height:auto">
	      <tbody ng-repeat="(key,value) in results" id="{{key}}_results" ng-show="display == '{{key}}'">
		<tr ng-repeat="(index,result) in value">
		  <td valign="top" school="{{result.school.replace(/\s/g,'_')}}" department="{{result.department.replace(/\s/g,'_')}}">
		    <div class="result-header">
		      <table class="match-parent">
			<tr>
			  <td align="center" valign="middle">
			    <span ng-class="{'glyphicon glyphicon-chevron-down': index==0,'glyphicon glyphicon-chevron-right': index!=0}" name="{{index==0?'open':'closed'}}"  onclick="toggle(this)" style="margin-right:0.5em;cursor:pointer"></span>
			  </td>
			  <td align="left" valign="middle" style="width:75%;">			   
			    <h6 title="{{result.name}}" style="margin-left:0.5em">
			      {{result.name}}
			    </h6>
			  </td>
			  <td align="right" valign="middle" style="width:20%;padding-right:2%;">
			    <span class="glyphicon glyphicon-star pl-hover" title="Save this result"></span>
			    <span class="glyphicon glyphicon-envelope pl-hover" title="Contact this advisor" ng-show="'{{key}}' == 'advisors'"></span>	    
			    <a href="../../single_advisor_display.php?id={{result.id}}" ng-show="'{{key}}' == 'advisors'" target="_blank">
			      <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
			    </a>
			    <a href="../../single_course_display.php?id={{result.id}}" ng-show="'{{key}}' == 'courses'" target="_blank">
			      <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
			    </a>
			    <a href="../../single_thesis_display.php?id={{result.id}}" ng-show="'{{key}}' == 'theses'" target="_blank">
			      <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
			    </a>
			    <a href="../../single_grant_display.php?id={{result.id}}" ng-show="'{{key}}' == 'grants'" target="_blank">
			      <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
			    </a>
			  </td>
			</tr>
		      </table>
		    </div>
		    <div ng-class="{'show result-body': index==0,'hide result-body': index!=0}">
		      <table class="match-parent">
			<tr>
			  <td>
			    <img src="{{result.picture.replace('.png','Red.png')}}" width="50" />
			  </td>
			  <td>
			    {{result.description}}
			    <br/>
			    <a href="../../single_advisor_display.php?id={{result.id}}" ng-show="'{{key}}' == 'advisors'" target="_blank">
			      See Details
			    </a>
			    <a href="../../single_course_display.php?id={{result.id}}" ng-show="'{{key}}' == 'courses'" target="_blank">
			      See Details
			    </a>
			    <a href="../../single_thesis_display.php?id={{result.id}}" ng-show="'{{key}}' == 'theses'" target="_blank">
			      See Details
			    </a>
			    <a href="../../single_grant_display.php?id={{result.id}}" ng-show="'{{key}}' == 'grants'" target="_blank">
			      See Details
			    </a>
			  </td>
			</tr>
		      </table>
		    </div>
		  </td>
		</tr>
	      </tbody>
	    </table>
	  </div>
	</div>
      </div>
    </div>
  </div>
  <script type="text/javascript">
   <!--
   $("#results_container").css("height",$(window).height()-150+"px");
   -->
  </script>
</body>
</html>
