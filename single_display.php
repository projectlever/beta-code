<?php 
session_start();
?>
<!DOCTYPE html>
<html ng-app="plAdvisor" ng-controller="mainController as controller">
<head>
  <title><?php echo $_GET['type']; ?></title>
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
  <link type="text/css" href="css/match.css" rel="stylesheet"/>
  <link type="text/css" href="css/single_advisor_viz.css" rel="stylesheet"/>
  <link type="text/css" href="css/single_display.css" rel="stylesheet"/>
  
  <!-- Le Scripts-->		
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.js"></script>
  <script src="http://d3js.org/d3.v3.min.js"></script>
  <script type="text/javascript" src="js/hello.min.js"></script>
  <?php 
  if ( !(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]==true) ){ 
    echo "<script>var loggedIn = false;var testDrive = true;</script>";
  }
  else {
    echo "<script>var loggedIn = true;var testDrive = false;</script>";
  }
  ?>
  <?php 
  echo "<script type='text/javascript'>var advisorId=".$_GET['id'].";var pageType='".$_GET["type"]."';
</script>";
  ?>
  <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20130526/json2.min.js"></script>
  <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>    
  <script type="text/javascript" src="js/single_display.js"></script>
  <script type="text/javascript" src="js/match.js"></script>
  <script type="text/javascript" src="js/directives/navbar.js"></script>
  <script type="text/javascript" src="js/directives/displayType.js"></script>
  <script type="text/javascript" src="js/directives/matchResults.js"></script>
  <script type="text/javascript" src="js/services/common.js"></script>
  <script type="text/javascript" src="js/services/resourceMatch.js"></script>
  <script type="text/javascript" src="js/single_advisor_viz.js"></script>
  
  <!-- Le Maps -->
  <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.map"></script>
</head>
<body class="pl-body">
  <div id="loading_sign">
    <!-- Loading gif -->
    <img src="http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif" class="loading-gif" />
  </div>
  <!-- NAVBAR -->
  <lever-navbar></lever-navbar>
  <!-- END NAVBAR, START BODY -->
  <display-type></display-type>
  <!-- LOGIN OVERLAY -->
  <?php 
  include("html/login_overlay.php");
  ?>
    
  <!-- Checks if Joomla 2.5 or lower or 3.0 or higher is in use and if the jQuery frameworks was already loaded to avoid incompatibility issues -->
  <script src="/templates/goodkarma/js/jquery.flexslider-min.js"></script>
</body>
</html>
