<html>
<head>
  <title>Project Lever</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="keywords" content="mentorship, collaboration, academia, guide, thesis, writing, library, research">
  <meta name="rights" content="Project Lever LLC">
  <meta name="description" content="Online platform for collaboration in universities.">
  
  <!-- Le Styles -->

  <link type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans|Raleway:400,300,500|Oxygen:400" rel="stylesheet">		
  <link type="text/css" href="css/normalize.css" rel="stylesheet"/>
  <link type="text/css" href="css/bootstrap/css/bootstrap.css" rel="stylesheet"/>
  <link type="text/css" href="templates/goodkarma/css/bootstrap_extend.css" rel="stylesheet"/>
  <link type="text/css" href="templates/goodkarma/css/style.css" rel="stylesheet"/>
  <link type="text/css" href="templates/goodkarma/css/flexslider.css" rel="stylesheet"/>    
  <link type="text/css" href="css/profile.css" rel="stylesheet" />
  <link type="text/css" href="css/match_page.css" rel="stylesheet" />
  <link type="text/css" href="css/home_page.css" rel="stylesheet"/>

  
  <!-- Le Scripts-->		
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
  <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>    
  <?php
  if ( !(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]==true) ){
    echo "<script>var loggedIn = false;var testDrive = true;</script>";
  }
  else {
    echo "<script>var loggedIn = true;var testDrive = false;</script>";
  }
  ?>  

<script type="text/javascript" src="js/hello.min.js"></script>
  <script type="text/javascript" src="js/general.js"></script>
  <script type="text/javascript" src="js/pl-match.js"></script>
  <script type="text/javascript" src="js/pl.js"></script>
  <script type="text/javascript" src="js/pl-home-main.js"></script>

  <!-- DIRECTIVES -->
  <script type="text/javascript" src="js/directives/navbar.js"></script>
  <script type="text/javascript">
   var homePage = true;
   var ignore = true;
  </script>




  <!-- SERVICES -->
  <script type="text/javascript" src="js/services/register.js"></script>
  <script type="text/javascript" src="js/services/common.js"></script>
</head>
