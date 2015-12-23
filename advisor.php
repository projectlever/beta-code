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
  <link type="text/css" href="css/advisor.css" rel="stylesheet"/>
  <link type="text/css" href="css/single_advisor_viz.css" rel="stylesheet"/>
  
  <!-- Le Scripts-->		
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.js"></script>
  <script src="http://d3js.org/d3.v3.min.js"></script>
  <?php 
  echo "<script type='text/javascript'>var advisorId=".$_GET['id'].";</script>";
  ?>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20130526/json2.min.js"></script>
  <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>    
  <script type="text/javascript" src="js/advisor.js"></script>
  <script type="text/javascript" src="js/directives/navbar.js"></script>
  <script type="text/javascript" src="js/services/common.js"></script>
  <script type="text/javascript" src="js/single_advisor_viz.js"></script>
  
  <!-- Le Maps -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.map"></script>
</head>
<body class="pl-body" ng-app="plAdvisor" ng-controller="mainController as controller">
  <!-- Loading gif -->
  <img src="http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif" class="loading-gif" />

  <!-- NAVBAR -->
  <lever-navbar></lever-navbar>
  <!-- END NAVBAR, START BODY -->
    
  <div class="pl-content pl-zebra" id="profile-header">
    <div class="container full-width full-height">
      <div class="row full-height">
	<div class="col-xs-2 col-xs-offset-1 full-height">
	  <img class="avatar" ng-if="common.alphaExists(data.advisor.picture) == true" src="{{verifyImage(data.advisor.picture)}}" alt="Advisor's Profile Picture" width="150px" border-radius="15px" border-color="#000" />	
	  <img class="avatar" ng-if="common.alphaExists(data.advisor.picture) == false" src="/images/LittleAdvisorRed.png" alt="Advisor's Profile Picture" width="150px" border-radius="15px" border-color="#000" />	
	</div>
	<div class="col-xs-8 col-xs-offset-0 full-height">
	  <h2>{{data.advisor.name}}</h2>
	  <h4>{{data.advisor.header}}</h4>
	  <h5> 
	    <span ng-if="data.advisor.department.length > 0">{{data.advisor.department.join(', ')}} |</span>
	    <span ng-if="data.advisor.school.length > 0">{{data.advisor.school.join(', ')}} |</span>
	    <span ng-if="common.alphaExists(data.advisor.university) == true">{{data.advisor.university}}</span>
	  </h5>
	  <span ng-if="data.advisor.email.length > 0"> 
	    <img src="http://www.icon2s.com/wp-content/uploads/2013/07/ios7-message-icon.png" width="25px" /> 
	    <a ng-repeat="email in data.advisor.email" href="mailto:{{email}}">
	      {{email}}{{$last ? '' : ','}}
	    </a>
	    <br/>
	  </span> 
	  <span ng-if="common.alphaExists(data.advisor.link) == true"> 
	    <img src="https://cdn2.iconfinder.com/data/icons/picons-essentials/57/website-512.png" width="25px" /> 
	    <a href="{{data.advisor.link}}">Website</a> 
	    <br/>
	  </span> 
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
            <li class="tabnav-tab" ng-click="selected = 'bio'" ng-class="{'selected':selected=='bio'}">
              <a href="">
		<span class="octicon octicon-diff-added"><img src="http://www.patong.fr/images/icon-people.png" width="25px"/></span>
		Bio
              </a>
            </li>
            <li class="tabnav-tab " ng-click="selected = 'viz'" ng-class="{'selected':selected=='viz'}">
              <a>
		<span class="octicon octicon-repo"><img src='https://camo.githubusercontent.com/48ebb6c0a04a056696bfe77658e890dd5284e4be/68747470733a2f2f7261772e6769746875622e636f6d2f466f64792f56697375616c697a652f6d61737465722f49636f6e732f7061636b6167655f69636f6e2e706e67' width="25px"/></span>
		Visualization
              </a>
            </li>
            <li class="tabnav-tab " ng-click="selected = 'network'" ng-class="{'selected':selected=='network'}">
              <a>
		<span class="octicon octicon-repo"><img src="https://cdn3.iconfinder.com/data/icons/monosign/142/users-512.png" width="25px"/></span>
		Similar Advisors
              </a>
            </li>
          </ul>        
	</div>
      </div>
    </div>
  </div>
  
  
    <!-- BIO DETAILS HERE -->
    <div class="pl-content pl-zebra" id="profile-details">
      <div class="container full-width full-height" id="profile-details" ng-show="selected == 'bio'">
	<div class="row full-height">
	  <div class="col-xs-10 col-xs-offset-1 full-height">
	    <div class="bio">
	      <p data-ng-bind-html="getHTML(data.advisor.info)"></p>
	      <p data-ng-bind-html="getHTML(data.advisor.block)"></p>
	    </div>
	
	    <div ng-repeat="(key,value) in data" ng-if="key != 'advisor' && value.length > 0 && key != 'weights'" class="{{key.toLowerCase().replace(' ','-')}}">
	      <h5>{{key}}</h5>
	      <ul>
		<li ng-repeat="(infoKey, infoValue) in value">
		  <a href="{{infoValue.link}}">{{infoValue.name}}</a>
		  <br/>
		  <strong>Description:</strong>
		  <br/>
		  <div data-ng-bind-html="getHTML(infoValue.description)"></div>
		  <br/>
		  <div ng-if="key == 'Funding'">
		    Co-PI: {{infoValue.coPiNames}}
		  </div>
		</li>
	      </ul>
	    </div>
	  </div>
	</div>	
      </div>
      <div class="container full-width full-height" id="visualization" ng-show="selected == 'viz'">
	<div class="row full-height">
	  <div class="col-xs-10 col-xs-offset-1 full-height text-center">
	    <div class="bio" id="viz">
	      
	    </div>
	  </div>
	</div>
      </div>
    </div>
    <!-- ##################################################### END HERE ##################################################### -->
  </div>
    
  <!-- Checks if Joomla 2.5 or lower or 3.0 or higher is in use and if the jQuery frameworks was already loaded to avoid incompatibility issues -->
  <script src="/templates/goodkarma/js/jquery.flexslider-min.js"></script>
</body>
</html>
