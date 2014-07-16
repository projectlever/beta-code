<!DOCTYPE html>
<head>
  <title>Advisor</title>
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
  <link type="text/css" href="css/match_page.css" rel="stylesheet"/>
  <link type="text/css" href="css/profile.css" rel="stylesheet"/>
  
  <!-- Le Scripts-->		
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.min.js"></script>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20130526/json2.min.js"></script>
  <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>    
  <script type="text/javascript" src="js/profile.js"></script>
  <script type="text/javascript" src="js/directives/navbar.js"></script>
  <script type="text/javascript" src="js/directives/displayType.js"></script>
  <script type="text/javascript" src="js/directives/autogrow.js"></script>
  <script type="text/javascript" src="js/services/common.js"></script>

  <!-- Le Maps -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.map"></script>
</head>
<body class="pl-body" ng-app="profile" ng-controller="ProfileController as controller">
  <!-- Loading gif -->
  <img src="http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif" class="loading-gif" />

  <!-- NAVBAR -->
  <lever-navbar></lever-navbar>
  <!-- END NAVBAR, START BODY -->
  
  <div class="pl-content pl-zebra" id="profile-header">
    <div class="container full-width full-height">
      <div class="row full-height">
	<div class="col-xs-2 col-xs-offset-1 full-height">
	  <img class="avatar" src="{{user.picture}}" width="150px" border-radius="15px" border-color="#000" />	
	</div>
	<div class="col-xs-6 col-xs-offset-0 full-height">
	  <h2>{{user.name}}</h2>
	  <h4> 
	    <span ng-if="user.department != null && user.department != 'empty'">{{user.department}} |</span>
	    <span ng-if="user.school != null">{{user.school}} |</span>
	    <span ng-if="user.university != null">{{common.replaceAll("_"," ",user.university)}}</span>
	    <br/>	  
	  </h4>
	  <span id="cv_display" ng-if="user.cvLink != null"> 
	    Current Topic Outline - 
	    <a href='{{user.cvLink}}' target='_blank'>{{user.cvName}}</a> 
	    <br/>
	  </span> 
	  <span ng-if="user.email != null"> 
	    <a href='mailto:{{user.email}}' target='_blank'>
	      <img src="http://www.icon2s.com/wp-content/uploads/2013/07/ios7-message-icon.png" width="25px" />
	    </a> 
	  </span>
	  <span ng-if="user.linkedIn != null"> 
	    <a href='{{user.linkedIn}}' target='_blank'>
	      <img src="http://artstarcustompaintworks.com/wp-content/uploads/2012/05/Linked_in.png" width="30px" />
	    </a> 
	  </span>
	</div>
	<div class="col-xs-2 col-xs-offset-0 full-height">
	  <div class="userEdit"> 
	    <button> 
	      <span> Edit Profile </span> 
	    </button> 
	  </div>
	</div>
      </div>
    </div>
  </div>
  
  <!-- NAV HERE -->
  <div class="pl-content pl-zebra" id="profile-match-nav">
    <div class="container full-width full-height">    
      <div class="row full-height">
	<div class="col-xs-6 col-xs-offset-3 full-height">	  
	  <ul class="tabnav-tabs">
            <li class="tabnav-tab" ng-click="selected = 'savedResources'" ng-class="{'selected':selected=='savedResources'}">
              <a href="">
              <span class="octicon octicon-diff-added"></span>
              <strong class="stat-count">6</strong> starred resources
              </a>
            </li>
            <li class="tabnav-tab " ng-click="selected = 'recommendedResources'" ng-class="{'selected':selected=='recommendedResources'}">
              <a>
		<span class="octicon octicon-repo"></span>
		<strong class="stat-count">25</strong> recommended resources
              </a>
            </li>
            <li class="tabnav-tab" ng-click="selected = 'researchProfile'" ng-class="{'selected':selected=='researchProfile'}">
              <a>
		<span class="octicon octicon-diff-added">
		  <img src="http://icons.iconarchive.com/icons/visualpharm/icons8-metro-style/512/Industry-Research-icon.png" width="28px"/>
		</span>
              	Research profile
		<span class="octicon opticon-diff-added" id="save_icon">
		  <img src="http://happytapper.com/wordpress/happytapper/wp-content/uploads/2013/05/flaticon-save.png" width="28px" 
		       ng-show="matchTextEdited == true" ng-click="saveMatchText();" />
		</span>
              </a>
            </li>
          </ul>          
        </div>
      </div>
    </div>
  </div>
    
    
  <!-- MATCHES HERE -->
  <div class="pl-content pl-zebra" id="profile-match">
    <div class="container full-width full-height" ng-show="selected == 'savedResources'">
      <div class="row full-height">
	<div class="col-xs-2 col-xs-offset-1 full-height">
	  <ul id="results_counter">
	    <li id="advisors_results_count" class="selected-result-type" ng-click="showResource('advisors')" ng-class="{'selected-result-type':display=='advisors','result-type':display!='advisors'}">
	      <img src="/images/LittleAdvisorRed.png" width="25">
	      Advisors
	      <span>{{results.advisorsNumResults}}</span>
	    </li>
	    <li id="courses_results_count" class="result-type" ng-click="showResource('courses')" ng-class="{'selected-result-type':display=='courses','result-type':display!='courses'}">
	      <img src="/images/LittleCourseRed.png" width="25">
	      Courses
	      <span>{{results.coursesNumResults}}</span>
	    </li>
	    <li id="theses_results_count" class="result-type" ng-click="showResource('theses')" ng-class="{'selected-result-type':display=='theses','result-type':display!='theses'}">
	      <img src="/images/LittleThesisRed.png" width="25">
	      Theses
	      <span>{{results.thesesNumResults}}</span>
	    </li>
	    <li id="grants_results_count" class="result-type" ng-click="showResource('grants')" ng-class="{'selected-result-type':display=='grants','result-type':display!='grants'}">
	      <img src="/images/LittleGrantRed.png" width="25">
	      Grants
	      <span>{{results.grantsNumResults}}</span>
	    </li>
	  </ul>	    
	</div>
      </div>    
    </div>
    <div class="container full-width full-height" ng-show="selected == 'researchProfile'">
      <div class="row">
	<div class="col-xs-8 col-xs-offset-2" style="padding-top:2em;">
	  <textarea auto-grow="hide:false;maxLines:1000;" class="search-bar" style="border:1px solid #aaa;padding-right:0.5em;" ng-keyup="matchTextEdited = true">{{user.block}}</textarea>
	</div>
      </div>
    </div>
  </div>	
  <!-- ##################################################### END HERE ##################################################### -->
    
  <!-- Checks if Joomla 2.5 or lower or 3.0 or higher is in use and if the jQuery frameworks was already loaded to avoid incompatibility issues -->
  
  <script src="/templates/goodkarma/js/jquery.flexslider-min.js"></script>
  
</body>
</html>
