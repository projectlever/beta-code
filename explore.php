<?php 
session_start();
?>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="generator" content="Bluefish 2.2.5" />
    <meta charset="utf-8" />

    <title>Explore</title>
    <link type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans|Raleway:400,300,500|Oxygen:400" rel="stylesheet" />
    <link type="text/css" href="css/normalize.css" rel="stylesheet" />
    <link type="text/css" href="css/bootstrap/css/bootstrap.css" rel="stylesheet" />
    <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/bootstrap_extend.css" rel="stylesheet" />
    <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/style.css" rel="stylesheet" />
    <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/flexslider.css" rel="stylesheet" />
    <link type="text/css" href="css/match_page.css" rel="stylesheet" />
    <link type="text/css" href="css/profile.css" rel="stylesheet" />
    <link rel="stylesheet" href="./explore/explore.css" type="text/css" />
    <link rel="stylesheet" href="css/search_bar.css" type="text/css" />

    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.19/angular.js" type="text/javascript">
    </script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript">
    </script>
    <script type="text/javascript" src="http://mbostock.github.com/d3/d3.v2.js">
    </script>
    <script type="text/javascript" src="js/hello.min.js"></script>
    <script type="text/javascript" src="explore/explore.js">
    </script>
    <script type="text/javascript" src="js/services/resourceMatch.js">
    </script>
    <script type="text/javascript" src="js/services/common.js">
    </script>
    <script type="text/javascript" src="js/directives/navbar.js">
    </script>
    <script type="text/javascript" src="js/directives/searchBar.js">
    </script>
    <script type="text/javascript" src="js/services/common.js"></script>
    <?php 
    if ( !(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]==true) ){ 
      echo "<script>var loggedIn = false;var testDrive = true;</script>";
    }
    else {
      echo "<script>var loggedIn = true;var testDrive = false;</script>";
    }
    ?>

</head>

<body ng-app="plExplore" ng-controller="explore as controller" style="position:absolute;height:135%;background-color:#fff">
    <lever-navbar></lever-navbar>
    <search-bar></search-bar>
    <div class="pl-content pl-zebra" style="top:150px;">
        <div class="content">
            <div class="row">
                <div class="col-xs-10 col-xs-offset-1">This is an interactive visualization that shows your matches visually. In the middle, you see the names of your professors. On the left, the departments that they are from; on the right, the keywords associated with their research. Click on any word to learn more. </div>
                <div class="col-xs-12 text-center" ng-show="loadingResults==false">
                    <a href="javascript:void(0)" onclick="window.location.reload()" style="position:absolute;left:25%;">See All</a>

                    <div style="width:100%;height:100%;position:relative;text-align:center;top:50px">
                        <div id="graph"></div>
                    </div>
                </div>
                <div class="col-xs-12 text-center" style="top:100px;" ng-show="loadingResults==true">
		  <img src="http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif" width="20" />                
                </div>
            </div>
        </div>
    </div>
    <!-- LOGIN OVERLAY -->
    <?php 
    include("html/login_overlay.php");
    ?>
</body>

</html>
