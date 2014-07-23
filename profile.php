<?php session_start(); if ( !(isset($_SESSION[ "loggedin"]) && $_SESSION[ "loggedin"]==true) ){ $_SESSION[ "return_url"]="http://projectlever.com/beta-code/profile.php" ; header( "Location: ../webfiles/login/login/"); } ?>
<!DOCTYPE html>

<head>
    <title>Profile</title>
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
    <link type="text/css" href="css/match_page.css" rel="stylesheet" />
    <link type="text/css" href="css/profile.css" rel="stylesheet" />

    <!-- Le Scripts-->
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.16/angular.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/json2/20130526/json2.min.js"></script>
    <script type="text/javascript" src="https://www.youtube.com/iframe_api"></script>
    <script type="text/javascript" src="js/profile.js"></script>

    <!-- DIRECTIVES -->
    <script type="text/javascript" src="js/directives/navbar.js"></script>
    <script type="text/javascript" src="js/directives/displayType.js"></script>
    <script type="text/javascript" src="js/directives/autogrow.js"></script>
    <script type="text/javascript" src="js/directives/matchResults.js"></script>

    <!-- SERVICES -->
    <script type="text/javascript" src="js/services/common.js"></script>
    <?php $set=isset($_GET[ "edit"]); $set=$set==1?1:0; echo "<script>var editMode = ".$set. ";</script>"; ?>

    <!-- Le Maps -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.map"></script>
</head>

<body class="pl-body" ng-app="profile" ng-controller="ProfileController as controller">
    <!-- Loading gif -->
    <img src="http://upload.wikimedia.org/wikipedia/commons/2/27/Throbber_allbackgrounds_eightbar.gif" class="loading-gif" />

    <!-- NAVBAR -->
    <lever-navbar></lever-navbar>
    <!-- END NAVBAR, START BODY -->
    <form id="save_form" class="container" action="php/save_profile.php" method="post" enctype="multipart/form-data">
        <div class="pl-content pl-zebra" id="profile-header">
            <div class="container full-width full-height">
                <div class="row full-height" ng-show="editMode == true">
                    <div class="col-xs-3 full-height">
                        <div class="profile_image">
                            <img class="avatar" src="../images/LittleAdvisorRed.png" width="150px" border-radius="15px" border-color="#000" style="display: block;">
                            <div class="profile-image-overlay" ng-show="editMode == true"></div>
                            <div class="edit-mode-button-container" ng-show="editMode == true">
                                <button class="edit-mode-button" type="button" onclick="$('#profile_image_button').trigger('click')">
                                    <div style="color: #fff;font-size: 20px;font-weight: bold;text-align: center;">
                                        <span class="glyphicon glyphicon-camera" style="font-size: 36px;"></span>
                                        <p>Add a profile photo</p>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div style="height:0px;overflow:hidden">
                            <input type="file" id="profile_image_button" style="width:100px" name="profile_image" />
                        </div>
                    </div>
                    <div class="col-xs-6 col-xs-offset-0 full-height" style="padding-left:0" ng-show="editMode == true">
                        <div class="row">
                            <div class="input-container" style="margin-top:0;margin: 0;margin-bottom: 5px;position: relative;width: 50%;">
                                <input type="text" id="user_name" name="Name" placeholder="Name" style="margin-top:1em" value="{{user.name}}">
                            </div>
                            <div class="input-container" style="margin-top:0;margin-top: 0; margin: 0; margin-bottom: 5px; padding-top: 12px;width: 50%;position: relative;">
                                <input type="text" id="email" name="Email" placeholder="Email" style="margin-top:1em" value="{{user.email}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-container" style="margin-top:0;margin-top: 0; margin: 0; margin-bottom: 5px; padding-top: 20px; float: left;width: 45%;position: relative;">
                                <select id="school_select" name="School" style="border: 1px solid #7D0000;height: 24px;background: #fff;padding: 2px;">
                                    <option>
                                        Select School
                                    </option>
                                    <option ng-repeat="school in schools" id="{{common.replaceAll(' ','_',school)}}" name="{{isSelected(school,'school',user.school)}}">
                                        {{school}}
                                    </option>
                                </select>
                            </div>
                            <div class="input-container" style="margin-top:0;margin-top: 0; margin: 0; margin-bottom: 5px; padding-top: 20px; /* display: inline; */width: 45%;float: right;position: relative;">
                                <select id="department_select" name="Department" style="border: 1px solid #7D0000; height: 24px; background: #fff; padding: 2px;">
                                    <option>
                                        Select Department
                                    </option>
                                    <option ng-repeat="(dept,sch) in departments" id="{{common.replaceAll(' ','_',dept)}}" name="{{isSelected(dept,'department',user.department)}}">
                                        {{dept}}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-container" style="margin-top:0;margin-top: 0; margin: 0; margin-bottom: 5px; padding-top: 20px; /* display: inline; */width: 45%;/* float: right; */position: relative;">
                                <input type="file" id="cv_display" name="cv" style="border-bottom: none;">
                                <label for="cv_select" style="font-size: 11px;top: 0px;">CV</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-2 col-xs-offset-0 full-height">
                        <div class="userEdit">
                            <button ng-if="editMode == true" ng-click="validate()">
                                <span>Save Profile</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row full-height" ng-show="editMode == false">
                    <div class="col-xs-3 col-xs-offset-0">
                    		<img src="https://media.licdn.com/mpr/mpr/shrink_200_200/p/6/000/221/025/2ca2795.jpg" class="profile_image" />
                    </div>
                    <div class="col-xs-6 col-xs-offset-0 full-height" ng-show="editMode == false">
                        <h2 style="margin-top:1em">{{user.name}}</h2>
                        <h4> 
	    		<span ng-if="user.department != null && user.department != 'empty'">{{user.department}} |</span>
	    		<span ng-if="user.school != null">{{user.school}} |</span>
	    		<span ng-if="user.university != null">{{common.replaceAll("_"," ",user.university)}}</span>
	  		  </h4>
                        <span id="cv_display" ng-if="user.cvLink != null"> 
	    			Current Topic Outline - 
	    			<a href='{{user.cvLink}}' target='_blank'>{{user.cvName}}</a> 
				    <br/>
			  </span> 
           <!--<span ng-if="user.email != null"> 
				    <a href='mailto:{{user.email}}' target='_blank'>
				      <img src="http://www.icon2s.com/wp-content/uploads/2013/07/ios7-message-icon.png" width="25px" />
				    </a> 
			  </span>-->
                        <span ng-if="user.linkedIn != null"> 
				    <a href='{{user.linkedIn}}' target='_blank'>
				      <img src="http://artstarcustompaintworks.com/wp-content/uploads/2012/05/Linked_in.png" width="30px" />
				    </a> 
			  </span>
                    </div>
                    <div class="col-xs-2 col-xs-offset-0">
                        <div class="userEdit">
                            <button ng-click="setEditMode(true)" ng-if="editMode == false">
                                <span> Edit Profile </span> 
                            </button>
                        </div>
                    </div>
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
              			Saved Resources
              		</a>
                                <span class="octicon opticon-diff-added" id="save_icon" ng-show="savesEdited == true">
							<img src="http://happytapper.com/wordpress/happytapper/wp-content/uploads/2013/05/flaticon-save.png" width="28px" title="Save Changes to Saved Resources" ng-click="removeFavorites();" />
	      			</span>
                            </li>
                            <li class="tabnav-tab" ng-click="selected = 'researchProfile'" ng-class="{'selected':selected=='researchProfile'}">
                                <a>
                                    <span class="octicon octicon-diff-added">
		  						<img src="http://icons.iconarchive.com/icons/visualpharm/icons8-metro-style/512/Industry-Research-icon.png" width="28px"/>
							</span>
                                    Research Profile
                                    <span class="octicon opticon-diff-added" id="save_icon">
		  						<img src="http://happytapper.com/wordpress/happytapper/wp-content/uploads/2013/05/flaticon-save.png" width="28px" 
		       					ng-show="matchTextEdited == true" ng-click="saveMatchText();" />
							</span>
                                </a>
                            </li>
                            <li class="tabnav-tab " style="display:none" ng-click="selected = 'recommendedResources'" ng-class="{'selected':selected=='recommendedResources'}">
                                <a>
							Recommended Resources
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
                <match-results></match-results>
            </div>
            <div class="container full-width full-height" ng-show="selected == 'researchProfile'">
                <div class="row">
                    <div class="col-xs-8 col-xs-offset-2" style="padding-top:2em;">
                        <h4 style="text-align:center;text-transform:none;">You may fill out your research profile here...</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-8 col-xs-offset-2" style="padding-top:2em;">
                        <textarea auto-grow="hide:false;maxLines:1000;" name="research" class="search-bar" style="border:1px solid #aaa;padding-right:0.5em;height:10em;" ng-keyup="matchTextEdited = true">{{user.block}}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- ##################################################### END HERE ##################################################### -->

    <!-- Checks if Joomla 2.5 or lower or 3.0 or higher is in use and if the jQuery frameworks was already loaded to avoid incompatibility issues -->

    <script src="/templates/goodkarma/js/jquery.flexslider-min.js"></script>

</body>

</html>
