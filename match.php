<?php  
// Session is started in the include file
include("php/match-page.php");
if ( isset($_GET["test-drive"]) )
  echo "<script>var testDrive = true;</script>";
else
  echo "<script>var testDrive = false;</script>";

if ( isset($_POST["search-query"]) )
  echo "<script>var initQuery = '".$_POST["search-query"]."';</script>";
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
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20130526/json2.min.js"></script>
  <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>    
  <script type="text/javascript" src="js/general.js"></script>
  <script type="text/javascript" src="js/pl-match.js"></script>
  <script type="text/javascript" src="js/match.js"></script>

  <!-- Le Maps -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.map"></script>
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
  <div class="pl-content pl-zebra" id="search_bar_container">
    <div class="container full-width full-height">
      <div class="row full-height">
	<div class="col-xs-10 col-xs-offset-1 full-height">
	  <table class="match-parent">
	    <tr>
	      <td>
		<textarea class="search-bar" id="search_box" placeholder="Tell us about your interests!" auto-grow="5"></textarea>		
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
	<div class="row full-height" style="padding-top:2em">
	  <div class="col-xs-2 col-xs-offset-1 full-height" style="padding-top:4em">
	    <ul id="results_counter">
	      <li id="advisors_results_count" class="selected-result-type" ng-click="showResource('advisors')" ng-class="{'selected-result-type':display=='advisors','result-type':display!='advisors'}">
		<img src="/images/LittleAdvisorRed.png" width="25">
		Advisors
		<span>{{results.advisors.length}}</span>
	      </li>
	      <li id="courses_results_count" class="result-type" ng-click="showResource('courses')" ng-class="{'selected-result-type':display=='courses','result-type':display!='courses'}">
		<img src="/images/LittleCourseRed.png" width="25">
		Courses
		<span>{{results.courses.length}}</span>
	      </li>
	      <li id="theses_results_count" class="result-type" ng-click="showResource('theses')" ng-class="{'selected-result-type':display=='theses','result-type':display!='theses'}">
		<img src="/images/LittleThesisRed.png" width="25">
		Theses
		<span>{{results.theses.length}}</span>
	      </li>
	      <li id="grants_results_count" class="result-type" ng-click="showResource('grants')" ng-class="{'selected-result-type':display=='grants','result-type':display!='grants'}">
		<img src="/images/LittleGrantRed.png" width="25">
		Grants
		<span>{{results.grants.length}}</span>
	      </li>
	    </ul>
	    <table id="department_delims">
	      <tbody>
		<tr>
		  <td colspan="3">
		    <h5> Departments </h5>
		  </td>
		</tr>
		<tr ng-repeat="(key,department) in departments track by $index" ng-if="alphaExists(department)">
		  <td valign="top">
		    <input type="checkbox" value="{{department}}" ng-click="toggle(department)" check-list="delims.departments" name="department_names" id="delim_{{key}}" department="{{department.replaceAll(' ','_')}}" />
		  </td>
		  <td valign="top" style="padding-left:0.35em">
		    <label for="delim_{{key}}">{{department}}</label>
		  </td>
		  <td align="right" valign="middle">
		    {{resultsLength[display][department]}}
		  </td>
		</tr>
	      </tbody>
	    </table>
	  </div>
	  <div class="col-xs-7 col-xs-offset-1 full-height">
	    <table class="match-parent" style="height:auto">
	      <tbody ng-repeat="(key,value) in results" id="{{key}}_results" ng-show="display == '{{key}}'">
		<tr>
		  <td>
		    <h3 style="text-transform: capitalize;"> We've matched you to {{results[key].length}} {{key}} </h3>
		  </td>
		</tr>
		<tr ng-repeat="(index,result) in value" name="{{result.department.replaceAll(' ','_')}}">
		  <td valign="top" style="max-height:400px;overflow:hidden">
		    <div class="result-header">
		      <table class="match-parent">
			<tr>
			  <td align="center" valign="middle" name="opener" onclick="toggle($(this).find('span'))">
			    <span class="glyphicon glyphicon-chevron-right" name="closed"  data="no" resource-id="{{result.id}}" resource-type="{{key}}" onclick="toggle(this)" style="margin-right:0.5em;cursor:pointer"></span>
			  </td>
			  <td align="left" valign="middle" style="width:75%" onclick="toggle(this)" class="result-name">
			    <a href="../../single_advisor_display.php?id={{result.id}}" ng-show="'{{key}}' == 'advisors'" target="_blank">
			      <h4 title="{{result.name}}" class="ng-binding">
				{{result.name}}
			      </h4>
			    </a>
			    <a href="../../single_course_display.php?id={{result.id}}" ng-show="'{{key}}' == 'courses'" target="_blank">
			      <h4 title="{{result.name}}" class="ng-binding">
				{{result.name}}
			      </h4>
			    </a>
			    <a href="../../single_thesis_display.php?id={{result.id}}" ng-show="'{{key}}' == 'theses'" target="_blank">
			      <h4 title="{{result.name}}" class="ng-binding">
				{{result.name}}
			      </h4>
			    </a>
			    <a href="../../single_grant_display.php?id={{result.id}}" ng-show="'{{key}}' == 'grants'" target="_blank">
			      <h4 title="{{result.name}}" class="ng-binding">
				{{result.name}}
			      </h4>
			    </a>
			    <h6><span ng-if="alphaExists(result.description)">{{snippet(result.description)}},</span> {{result.department}}</h6>
			  </td>
			  <td align="right" valign="middle" style="width:20%;padding-right:2%;" onclick="toggle(this)">
			    <table>
			      <tr>
				<td>
				  <span class="glyphicon glyphicon-star pl-hover" ng-class="{'starred':isSavedResource(result.id,key)}" title="Save this result" ng-click="toggleFavorite(result.id,key)"></span>
				</td>
				<td>
				  <a href="mailto:{{getEmail(result.email)}}" ng-if="key == 'advisors' && result.email != '{}' && result.email != ''">
				    <span class="glyphicon glyphicon-envelope pl-hover" title="Contact this advisor"></span>	    
				  </a>
				  <span class="glyphicon glyphicon-envelope" style="margin-left:0.75em;color:#f5f5f5" ng-if="key != 'advisors' || !alphaExists(result.email)"></span>
				</td>
				<td>
				  <a href="../../single_advisor_display.php?id={{result.id}}" ng-show="'{{key}}' == 'advisors'" target="_blank">
				    <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
				  </a>
				  <a href="../../single_course_display.php?id={{result.id}}" ng-show="'{{key}}' == 'courses'" target="_blank">
				    <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
				  </a>				  
				  <a href="../../single_thesis_display.php?id={{result.id}}" ng-show="'{{key}}' == 'theses'" target="_blank">
				    <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
				  </a>
				</td>
				<td>
				  <a href="../../single_grant_display.php?id={{result.id}}" ng-show="'{{key}}' == 'grants'" target="_blank">
				    <span class="glyphicon glyphicon-new-window pl-hover" title="View details"></span>
				  </a>
				</td>
			      </tr>
			    </table>
			  </td>
			</tr>
		      </table>
		    </div>
		    <div class="hide result-body">
		      <table class="match-parent">
			<tr>
			  <td valign="top">
			    <img src="{{result.picture.replace('.png','Red.png')}}" width="50" />
			  </td>
			  <td name="description_box">
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
