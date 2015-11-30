<?php // Session is started in the include file 
      include( "php/match-page.php"); 
      if ( isset($_POST[ "search-query"]) ) echo "<script>var initQuery = '".$_POST[ "search-query"]. "';</script>"; 
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
    <link type="text/css" href="css/normalize.css" rel="stylesheet" />
    <link type="text/css" href="css/bootstrap/css/bootstrap.css" rel="stylesheet" />
    <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/bootstrap_extend.css" rel="stylesheet" />
    <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/style.css" rel="stylesheet" />
    <link type="text/css" href="http://projectlever.com/templates/goodkarma/css/flexslider.css" rel="stylesheet" />
    <link type="text/css" href="css/socialicious/css/socialicious.css" rel="stylesheet" />
    <link type="text/css" href="css/match_page.css" rel="stylesheet" />

    <!-- Le Scripts-->
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20130526/json2.min.js"></script>
    <script type="text/javascript" src="js/hello.min.js"></script>
    <script type="text/javascript" src="js/general.js"></script>
    <script type="text/javascript" src="js/pl-match.js"></script>
    <script type="text/javascript" src="js/match.js"></script>
    <script type="text/javascript" src="js/directives/navbar.js"></script>

    <!-- SERVICES -->
    <script type="text/javascript" src="js/services/register.js"></script>
    <script type="text/javascript" src="js/services/common.js"></script>

    <!-- Le Maps -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.map"></script>
    <script>
     var ignore = true;
     var loggedIn = false;
    </script>
</head>

<body class="pl-body" ng-app="plMatch" ng-controller="MatchController as controller">
    <!-- Loading gif -->
    <img src="http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif" class="loading-gif" />
    <h3 id="match_page_intro">Let Us Match You to Advisors!</h3>
    <!-- NAVBAR -->
    <lever-navbar></lever-navbar>
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
                                        "font-size": $(".search-bar").outerHeight() - 12 + "px",
                                        /* 12 = 2px for both padding-top(bottom) + 1px for both border-top(bottom) + 1px for image border + 5px padding */
                                    }).parent().css("width", $(".search-button").width());
                                </script>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- RESULTS SECTION -->
    <?php include( "html/results_section_match_page.php"); ?>


    <!-- LOG IN OVERLAY -->
    <?php include( "html/login_overlay.php"); ?>
    <script type="text/javascript">
        <!--
        $("#results_container").css("height", $(window).height() - 150 + "px");
         -->
    </script>
</body>

</html>
