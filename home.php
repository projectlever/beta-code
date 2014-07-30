<?php 
session_start();
if ( (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"]==true) )
  header("Location: http://projectlever.com/beta-code/match.php");
?>
<!DOCTYPE html>
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
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.17/angular.js"></script>
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
<body class="pl-home" ng-app="plMatch" ng-controller="MatchController as controller">
  <img src="images/resources/infographics/down_arrow.png" class="down_arrow"></div>
  <lever-navbar></lever-navbar>
  <div class="header pl-frame pl-home-top-frame" id="frame_1">
    <div class="container">
      <div class="row">
	<div class="col-sm-12">
	  <div class="center" id="intro">
	    <h1>Project lever</h1>
	    <p class="lead">matching students to advisors</p>
	    <div>&nbsp;</div>
	    <div class="row text-center" class="match_container">
	      <div class="col-sm-6 col-xs-offset-3" style="margin-right:0;padding-right:0;">
		<table class="match-parent">
		  <tr>
		    <td valign="middle" align="right" style="width:100%;padding-right:0;padding-left:0;">
		      <form action="match.php?test_drive" method="post" id="test_drive">
			<textarea class="search-bar" name="search-query" id="search_box" placeholder="Tell us about your interests!" auto-grow="5"></textarea>		
		      </form>
		    </td>
		    <td align="left" valign="middle" style="padding-left:0;">
		      <span class="glyphicon glyphicon-search search-button" id="search_button" ng-click="search()"></span>
		    </td>
		  </tr>
		</table>
	      </div>
	    </div>
	  </div>
	</div>
      </div>
    </div>
  </div>  
  <div class="blurb pl-frame" id="frame_2">
    <div class="container">
      <div class="vert" style="padding-top:0">
        <div class="col-md-12 text-center" style="height:5%;">
          <h2 class="redfont">The Easiest Way to Start Research</h2>  
	  <br/>
        </div>
        <div class="col-md-12 text-center" style="height:70%;">
	  <img src="http://projectlever.com/images/Project Lever Graphic 1.png" id="youtube_vid_thumbnail" style="display:inline">
          <!----------- Youtube video HTML----------------->
<!--	  <iframe class="youtube-player" type="text/html" width="640" height="385" src="https://www.youtube.com/embed/UIT_rrgxXus?version=3&amp;enablejsapi=1&amp;playerapiid=ytplayer" allowfullscreen="" frameborder="0" id="video_screen"></iframe>--//-->
	</div>
	<div class="col-md-12 text-center" style="height:25%;">
	  <h3 class="redfont" style="text-align:left">
	    "Project Lever exemplifies the very best in educational technology thinking and design. It's a simple, 
	    brilliantly executed idea that could have a very real impact on college campuses."
	  </h3> 
          <h4 class="redfont">-Glenn B. Magid, Harvard, Assistant Dean of Advising</h4>
	</div>
      </div>
    </div>
  </div>
  <div class="featurette pl-frame" id="frame_3">
    <div class="container">
      <div class="center">
	<div class="row">
	  <div class="col-md-12 text-center">
            <h2>Better research for all</h2>	  
	  </div>
	</div>
	<div class="row target">
          <div class="col-md-2 col-md-offset-3 text-center">
            <div class="featurette-item"> 
	      <span class="glyphicon glyphicon-pencil"></span>   
              <h4>Students</h4>
              <p>More time spent on research, less on searching for resources</p>
            </div>
          </div>
          <div class="col-md-2 text-center">
            <div class="featurette-item"> 
	      <span class="glyphicon glyphicon-magnet"></span>
              <h4>Faculty</h4>
              <p>Better student-advisor match helps to optimize faculty investment into
		the right student.
	      </p>
            </div>
          </div>
          <div class="col-md-2 text-center">
            <div class="featurette-item"> 
	      <span class="glyphicon glyphicon-globe"></span>
              <h4>Universities</h4>
              <p>All university content, aggregated and organized in a matter of weeks,
		not months or years.</p>
            </div>
          </div>
	</div>
      </div>
    </div>
  </div>
  <div id="frame_4" class="blurb pl-frame">
    <div class="container">
      <div class="center">
	<div class="row">
          <div class="col-md-12 text-center">
	    <h2 class="redfont">What Everyone's Saying</h2>
	    <p class="lead"></p>
	  </div>
        </div>
        <div class="row target">
	  <div class="col-md-12 text-center">
	    <div class="col-md-4 text-center">
              <div class="featurette-item"> 
                <div class="speech-bubble quote1">
		  <p>
		    Project Lever is a tool that's gaining traction among numerous major universities who have heard for years how students struggle to find mentors and advisors for academic projects.
		  </p>
                </div>
		<img width="150" src="http://1z8j561n17ze14agfo2do77bd4g.wpengine.netdna-cdn.com/wp-content/uploads/Clayton-Christensen-logo.jpg">    
	      </div>
            </div>
            <div class="col-md-4 text-center">
              <div class="featurette-item"> 
                <div class="speech-bubble quote2">
		  <p>
		    Students at the School of General Studies will have an easier time finding grant opportunities and accessing faculty research with a new website, ProjectLever, which was launched this month.
		  </p>
                </div>
                <img width="150" src="http://www.nearbycafe.com/artandphoto/liuxiaphotos/wp-content/uploads/2012/04/columbia_spectator_logo.jpg">		
              </div>
            </div>
            <div class="col-md-4 text-center">
              <div class="featurette-item"> 
		<div class="speech-bubble quote3">
                  <p>
		    Though professors may spend their weeks lecturing, meeting with students, and mentoring advisees, much of their work outside of the classroom remains a mystery to most students.
		  </p>
                </div>
		<img width="75" src="http://static.thecrimson.com/images/seal.jpg">
              </div>
            </div>
<!--            <div class="col-md-3 text-center">
              <div class="featurette-item"> 
		<div class="speech-bubble">
                  <p>
		    Svetlana Dotsenko is the Founder and CEO of Project Lever, an educational technology company that matches students to advisors. 
		  </p>
                </div>
		<img width="150" src="http://cc-cdn.carecloud.com/wp-content/uploads/2013/08/boston.com_logo.png">
              </div>
            </div>-->
          </div>
        </div>	
      </div>
    </div>
  </div>
  <div class="featurette pl-frame" id="frame_5">
    <div class="container">
      <div class="center">
	<div class="row">
          <div class="col-md-12 text-center">
            <h2>Our Clients</h2>	  
          </div>
	</div>
	<div class="row target">
          <div class="col-md-2 col-md-offset-2 text-center">
            <div class="featurette-item">
              <img height="150" width="150" src="http://thejointblog.com/wp-content/uploads/2013/08/Harvard-Logo.png">
              <h4>Harvard</h4>	    
            </div>
          </div>
          <div class="col-md-2 text-center">
            <div class="featurette-item">
              <img height="150" width="150" src="http://www.cs.columbia.edu/database/images/logos/hammer.png">
              <h4>Columbia</h4>	    
            </div>
          </div>
          <div class="col-md-2 text-center">
            <div class="featurette-item">
              <img height="150" src="http://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Fordham_University_Logo.png/160px-Fordham_University_Logo.png">
              <h4>Fordham</h4>	    
          </div>
          </div>
          <div class="col-md-2 text-center">
            <div class="featurette-item">
              <img height="150" width="150" src="http://a.espncdn.com/combiner/i?img=/i/teamlogos/ncaa/500/87.png?w=200&amp;h=200&amp;transparent=true">
              <h4>Notre Dame</h4>	      
            </div>
          </div>
	</div>
      </div>
    </div>
  </div>
  <div class="blurb" id="frame_6">
    <div class="container">
      <div class="col-md-12 text-center">&nbsp;</div>
      <div class="row">
        <div class="col-md-6 col-md-offset-3 text-center">
          <h3>
	    Interested in learning more? 
	    <br/><br/>
	    <a href="mailto:info@projectlever.com">Contact Us.</a>
	  </h3>
          <p class="lead">
	    <a href="https://www.facebook.com/ProjectLever/">
	      <img src="images/thumbnails/icons/Facebook-Icon.png" class="social-icon" />
	    </a>
	    <a href="https://www.twitter.com/ProjectLever/">
	      <img src="images/thumbnails/icons/Twitter-Icon.png" class="social-icon" />
	    </a>
	  </p>
        </div>
      </div>
    </div>
  </div>
  <div class="footer featurette">
    <div class="container">
      <div class="row">
	<div class="col-xs-1 text-center">
	  <a href="http://projectlever.com/about.html">About</a>
	</div>
	<div class="col-xs-1 col-xs-offset-1 text-center">
	  <a href="http://projectlever.com/press.html">Press</a>
	</div>
	<div class="col-xs-2 col-xs-offset-2 text-center">
	  Project Lever <br/>
	  &copy; 2013-2014
	</div>
	<div class="col-xs-1 col-xs-offset-2 text-center">
	  <a href="http://projectlever.com/team.html">Team</a>
	</div>
	<div class="col-xs-1 col-xs-offset-1 text-center">
	  <a href="http://projectlever.com/join.html">Join</a>
	</div>
      </div>
    </div>
  </div>
  <?php 
  include("html/login_overlay.php");
  ?>
  <script>
   analytics.track('Visited Home', {
     
   });	
  </script>
</body>
</html>
