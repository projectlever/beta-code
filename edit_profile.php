<? 
    /**
     * Live: 2-4-2014
     * Version: 3.1
     */
    require("algorithm.php");
    session_start();
    
	if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
            header("Location: http://www.projectlever.com/webfiles/login/login/");
        }
    
    // Define some variables
    $categories = array("Advisor","Course","Thesis","Grant","Funding","Grad");
        
    $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
	if(mysqli_connect_errno($con))
		echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
	
	// Get user data
	$sql = "SELECT *
			FROM Users
			WHERE Email='$_SESSION[email]'";

	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_array($result);
	
	$name           = $row["Name"];
	$school         = $row["School"];
	$department     = $row["Department"];
	$focus          = $row["Focus"];	
	$interests      = $row["Interests"];
	$university     = $_SESSION['university'];
	$profileImage   = $row["Profile_Image"];
	$field          = $row["Field"];
	$delims         = $row["Delimiters"];
	$outline        = $row["Outlines"];

	$_SESSION['class'] = $row['Class'];
	$_SESSION['school'] = $school;
	$_SESSION['department'] = $department;
	
	// Get university Data				
	$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
	if(mysqli_connect_errno($con))
		echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
	
	// Get division data
	$sql = "SELECT *
		FROM `Advisor`
		WHERE `Department` = '".json_encode($department,true)."'";
	if(!$result = mysqli_query($con, $sql))
		echo mysqli_error($con);
	else{
		while($row = mysqli_fetch_array($result)){
			$_SESSION['division'] = $row['Division'];
			break;
		}
	}
	
	// Get correct buckets
	for($i = 0, $n = count($categories); $i < $n; $i++){
		$sql = "SELECT `".$categories[$i]."_ID`
				FROM `".$categories[$i]."`
				WHERE `University` = '".$university."'";
		if(!$result = mysqli_query($con,$sql))
			echo mysqli_error($con);
		elseif(mysqli_num_rows($result)==0)
			$categories[$i] = false;
	}
	
	// Get Browse and Crest
	$sql = "SELECT `Browse`,`Crest`
			FROM `Browse`
			WHERE `University` = '".$university."'";
	if(!$result = mysqli_query($con,$sql))
		echo mysqli_error($con);
	else
		$row = mysqli_fetch_array($result);
	$browse = $row["Browse"];
	$crest = $row["Crest"];
    
    // Get Schools
	$schools = array();
	$departments = array();
	    
	$univData = json_decode(file_get_contents("./import/data/".$university."/importers/schoolDepartmentData.json"),true);
	foreach ( $univData as $schoolName => $departmentNames ){
	    $schools[] = $schoolName;
	    for ( $i = 0, $n = count($departmentNames); $i < $n; $i++ ){
	        if (!$departments[$departmentNames[$i]]){
	            $departments[$departmentNames[$i]] = array($schoolName);
	        }
	        elseif(!in_array($schoolName,$departments[$departmentNames[$i]])){
	        	array_push($departments[$departmentNames[$i]],$schoolName);
	        }
	    }
	}
	
	asort($schools);
	ksort($departments);
	
	$dept = json_encode($departments);
	mysqli_close($con);
	
?>
<!DOCTYPE html>
	<html xml:lang="en-gb" lang="en-gb">
	<head> 
		<base href="http://projectlever.com/profile.php"/>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="keywords" content="mentorship, collaboration, academia, guide, thesis, writing, library"/>
		<meta name="rights" content="Project Lever LLC"/>
		<meta name="description" content="Online platform for collaboration in universities."/>
	
		<title>Edit</title>
	
		<!-- Le scripts -->
	
		<script src="/media/system/js/mootools-core.js" type="text/javascript"></script>
		<script src="/media/system/js/core.js" type="text/javascript"></script>
		<script src="/media/system/js/caption.js" type="text/javascript"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>  	
		<script src="/templates/goodkarma/js/bootstrap.min.js" type="text/javascript"></script>
		<script src="edit_profile.js" type="text/javascript"></script>
   
		<!-- Le styles -->

		<link href="http://projectlever.com/favicon.ico" rel="shortcut icon"/>
		<link rel="stylesheet" href="/plugins/content/xtypo/themes/default/style.css" type="text/css" />
		<link href="/templates/goodkarma/css/bootstrap.css" rel="stylesheet"/>
		<link href="/templates/goodkarma/css/bootstrap-responsive.css" rel="stylesheet"/>
		<link href="/templates/goodkarma/css/bootstrap_extend.css" rel="stylesheet"/>
		<link href="/templates/goodkarma/css/flexslider.css" rel="stylesheet"/>
		<link href="/templates/goodkarma/css/style.css" rel="stylesheet"/>
		<link href="edit_profile.css" rel="stylesheet"/>

		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- Le fav and touch icons -->
		
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png"/>
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png"/>
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png"/>
		<link rel="apple-touch-icon-precomposed" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-57-precomposed.png"/>

        <!--- BAROMETER FEEDBACK SCRIPT --->
        <style type='text/css'>@import url('http://getbarometer.s3.amazonaws.com/assets/barometer/css/barometer.css');</style>
        <script src='http://getbarometer.s3.amazonaws.com/assets/barometer/javascripts/barometer.js' type='text/javascript'></script>
        <script type="text/javascript" charset="utf-8">
           BAROMETER.load('bHQAerFMj8fLB3GQTcm1W');
           $ = jQuery;
           $("#barometer_tab").attr("href","javascript:void(0);");
        </script>
        <!--- END BAROMETER FEEDBACK SCRIPT --->
  
	</head>
	<body>
        <?php
            echo "<div id='university_name' style='display:none'>".$university."</div>";
        ?>
		<div class="sourrounding_all  not_on_frontpage">

			<!-- ************************ LOGO AREA START************************ -->
	
			<div class="sourrounding_wrapper logo_area_wrapper">
				<div class="inner_wrapper">
					<div class="container-fluid">	
						<div class="navbar">
							<div class="navbar-inner">
								<div class="container-fluid">
									<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
										<span class="icon-bar"></span>
									</a>
		  
									<!-- Logo as text taken from templates config-->
				  
									<a class="brand logo_text" href=""><h1>Project Lever</h1></a>								
									<div class="nav-collapse">
										<ul class="nav ">
											<li class="item-237 current active"><a href="/profile.php" style="color:#dddddd">Profile</a></li>
											<li class="item-247"><a href="/browse.php" style="color:#dddddd">Browse</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<!-- ************************ LOGO AREA END************************ -->

		<!-- ##################################################### HEADER AREA STARTS HERE ##################################################### -->
		
			<div class="sourrounding_wrapper header_area_wrapper">
				<form name="edit_profile" action="change_profile2.php" method="post" enctype="multipart/form-data" style="margin:0;" onsubmit="return validate()">
					<div class="inner_wrapper">
				
					<!-- ************************ TOP HEADER START************************ -->

						<div class="container-fluid header_modules_wrapper" style="background-color:#eeeeee;">  
							<div class="span0 moduletable module-column mod-201 no-title clearfix">
								<div class="module-block" style="background-color:#eeeeee">
									<div class="module-header" style="height:0px;">
									</div>
									<div class="module-content" style="height:130px">
										<div class="custom">
										</div>
										<div class="clear">
										</div>
									</div>
								</div>	
							</div>
						</div>
		
						<!-- ************************ HEADER MODULES END************************ -->

						<!-- ************************ SLIDER MODULES START************************ -->

						<!-- ************************ SLIDER MODULES END************************ -->

						<!-- ************************ BELOW_HEADER MODULES START************************ -->

						<div class="container-fluid below_header_modules_wrapper">  
							<div class="row-fluid">
								<div class="span0 moduletable module-column mod-204 span2 no-title clearfix">	
									<div class="module-block">	
										<div class="module-header">
											<h3 class="module-title"><span>User</span> Picture</h3>
										</div>			
										<div class="module-content">
											<div class="custom"  > 
												<img src=<?php 
    												if ($profileImage != ""){
    												    echo '"' . $profileImage .'"';
    												}
    												else {
    												    echo "/images/LittlePerson.png";
    												}
    											?> alt="" width="150px" border-radius="15px" />
												<br/>
												<span id="profile_image_upload" class="file-upload">
												    <input type="file" name="profile_image" id="profile_image" onchange="displayFileName(this,'Upload Profile Picture')" class="hidden_input" />
												    <span class="input-display" style="width:10em">Upload Profile Picture</span>
												</span>
											</div>
											<div class="clear">
											</div>
										</div>
									</div>	
								</div>
								<div class="span0 moduletable module-column mod-199 span8 no-title clearfix">
									<div class="module-block">
										<div class="module-header">
											<h3 class="module-title"><span>Profile</span> Page</h3>
										</div>
										<div class="module-content">
											<div class="custom">
												<h1 style="color:#dddddd"><input type="text" style="border-radius:5px" value=<? echo '"'.$name.'"'; ?> placeholder="My Name" name="Name" id="user_Name"></h1>
												<br/>
												<span id="cv_upload" class="file-upload">
												    <input type="file" name="cv" onchange="displayFileName(this,'Upload CV')" id="cv" class="hidden_input" />
												    <span class="input-display">Upload Topic Outline</span>
												</span>
												<span id="cv_display">
												    <?php
												        if ( $outline != "" ){
												            echo "<br/> Current Topic Outline - <a href='".$outline."' target='_blank'>";
												            $fileNames = json_decode(file_get_contents("./user-outlines/names.json"),true);
												            $fname = $fileNames[$outline];
												            if ( $fname != null ){
												                echo $fname . "</a>";
												            }
												            else {
												                echo $outline . "</a>";
												            }
												        }
												    ?>
												</span>
												<hr style="margin-top:20px; border-bottom:none; border-top:1px solid #ccc" />
												<h4 style="color:#dddddd">
													<span class='dropdown_new'>
														<select id="school_select" name="School" onchange="fillDepartments(this,true);">
															<option value="empty" name='School'>My School</option>
															<?
																for($i = 0, $n = count($schools); $i < $n; $i++){
																	echo "<option value='".$schools[$i]."' name='School'";
																	if ( $schools[$i] == $school ){
																	    echo " selected='selected' ";
																	}
																	echo ">".$schools[$i]."</option>";
																}
															?>
														</select>
													</span>
													<span class='dropdown_new'>
														<select id="department_select" name="Department">
															<option value="empty" name='Department'>My Department</option>
															<?php
															    foreach($departments as $departmentName=>$schoolName){
																    echo "<option value='".$departmentName."' style='display:none' name='hide' schools='".str_replace("-"," ",preg_replace("/\s/","_",implode("-",$schoolName)))."'";
																    if ( $school == $schoolName ){
																        echo " display-item='true' ";
																    }
																    else {
																        echo " display-item='false' ";
																    }
																    if ( $departmentName == $department ){
																        echo " selected='selected' ";
																    }
																    echo ">".$departmentName."</option>";
																}
															?>
														</select>
													</span>
												</h4>
											</div>
											<div class="clear">
											</div>
										</div>
									</div>	
								</div>
								<div class="span0 moduletable module-column mod-208 span2 no-title clearfix">
									<div class="module-block">
										<div class="module-header">
											<h3 class="module-title"><span>Button</span> (save)</h3>
										</div>
										<div class="module-content">
											<div class="custom">
												<? echo '<img src="'.$crest.'" width="150px"/>';?>
											</div>
											<div class="clear">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- ************************ BELOW_HEADER MODULES END************************ -->

					</div>
				</div>

				<!-- ##################################################### HEADER AREA ENDS HERE ##################################################### -->

				<!-- ##################################################### TOP AREA STARTS HERE ##################################################### -->
	
				<div class="sourrounding_wrapper top_area_wrapper">
					<div class="inner_wrapper">
						<div class="container-fluid">  
							<div class="row-fluid">
								<div class="span0 moduletable module-column mod-200 span8 clearfix">
									<div class="module-block">
										<div class="module-content">
											<div class="custom">         
												<input type="text" style="border-radius:5px;" name="field_of_study" id="field_of_study" placeholder="My field of study" class="text-input" value=<?php
												    if ( $field != "" ){
												        echo "'".$field."'";
												    }
												    else {
												        echo "''";
												    }
												?> />
												<div id="description">         
    												<textarea name="description" placeholder='<?php 
    												    echo "Type in your research interests here. For example,\"".
    												        "I am interested in studying the political implications of a major epidemic of a disease. I am thinking of". 
    												        "studying historical examples from the 20th century\", preferably in the Asian region.";
    												?>'><? echo ($interests != "") ? ($interests) : (''); ?></textarea>
    											</div>
												<br/>
												<input type="submit" id="edit_profile_submit" class="savebutton" value='Save' />
											</div>
											<div class="clear">
											</div>
										</div>
									</div>
								</div>
								<div class="span0 moduletable module-column mod-203 span4 clearfix">
									<div class="module-block">
										<div class="module-content">
											<div class="module-content">
												<div class="custom">
													<div id="study_field_description">
													    We'll use this information to suggest different tools use when
													    choosing resources.
													</div>
													<div id="research_deescription_help" style="margin-top:9em">
													    Please describe your research in one or two paragraphs. The more specific
													    you are, the better suggestions we can give to you when we match you with
													    professors.
													</div>
												</div>
												<div class="clear">
												</div>
											</div>
										</div>
									</div>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
	
			<!-- ##################################################### TOP AREA ENDS HERE ##################################################### -->

			<!-- ************************ MAIN AREA START************************ -->
  
			<!-- Main Content Area Start -->
			
			<!-- Main Content Area End -->
								
			<!-- ************************ MAIN AREA END************************ -->	

			<!-- ##################################################### FOOTER AREA STARTS HERE ##################################################### -->
		
			<div class="sourrounding_wrapper footer_area_wrapper">
				<div class="inner_wrapper">
					<div class="container-fluid">	
						<div class="row-fluid">
							<div class="span0 moduletable module-column mod-183 span6 clearfix">
								<div class="module-block">
									<div class="module-header">
										<h3 class="module-title"><span>Interested</span> in Learning More?</h3>
									</div>
									<div class="module-content">
										<div class="custom"> 
											<p style="color:#999999;">Feel free to discover more resources about our company.</p>
											<p style="color:#999999;">Based in Cambridge, MA</p>
											<p>
												<script type='text/javascript'>
													<!--
														var prefix = '&#109;a' + 'i&#108;' + '&#116;o';
														var path = 'hr' + 'ef' + '=';
														var addy93730 = '&#105;nf&#111;' + '&#64;';
														addy93730 = addy93730 + 'pr&#111;j&#101;ctl&#101;v&#101;r' + '&#46;' + 'c&#111;m';
														document.write('<a ' + path + '\'' + prefix + ':' + addy93730 + '\' style="color:#999999;">');
														document.write(addy93730);
														document.write('<\/a>');
														//-->\n 
												</script>
												<script type='text/javascript'>
													<!--
														document.write('<span style=\'display: none;\'>');
														//-->
												</script>This email address is being protected from spambots. You need JavaScript enabled to view it.
												<script type='text/javascript'>
													<!--
														document.write('</');
														document.write('span>');
													//-->
												</script>
											</p>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
							<div class="span0 moduletable module-column mod-184 span2 clearfix">
								<div class="module-block">
									<div class="module-header">
										<h3 class="module-title"><span>About</span> Us</h3>
									</div>
									<div class="module-content">
										<div class="custom">
											<p><a href="/press" style="color:#999999;">Press</a></p>
											<p><a href="/team" style="color:#999999;">Team</a></p>
											<p><a href="/index.php" style="color:#999999;">Blog</a></p>
										</div>
										<div class="clear">
										</div>
									</div>
								</div>	
							</div>
							<div class="span0 moduletable module-column mod-185 span2 clearfix">
								<div class="module-block">
									<div class="module-header">
										<h3 class="module-title"><span>Connect</span></h3>
									</div>
									<div class="module-content">
										<div class="custom">
											<p><a href="http://www.facebook.com/ProjectLever" style="color:#999999;">Facebook</a></p>
											<p><a href="https://twitter.com/projectlever" style="color:#999999;">Twitter</a></p>
											<p><a href="http://www.linkedin.com/company/2973250?trk=tyah" style="color:#999999;">LinkedIn</a></p>
											<p><a href="http://www.crunchbase.com/company/project-lever" style="color:#999999;">CrunchBase</a></p>
										</div>
										<div class="clear">
										</div>
									</div>
								</div>
							</div>
							<div class="span0 moduletable module-column mod-186 span2 clearfix">
								<div class="module-block">
									<div class="module-header">
										<h3 class="module-title"><a style="color:#999999;" href="webfiles/login/logout/logout.php">Log Out</a></h3>
									</div>
									<div class="module-content">
										<div class="custom">							
										</div> 
										<div class="clear">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<!-- ##################################################### FOOTER AREA ENDS HERE ##################################################### -->


		<!-- ##################################################### CREDITS AREA STARTS HERE ##################################################### -->
		
			<div class="sourrounding_wrapper credits_area_wrapper">
				<div class="inner_wrapper">
				</div>
			</div>
		
		<!-- ##################################################### CREDITS AREA ENDS HERE ##################################################### -->
		</div>
	<!-- Checks if Joomla 2.5 or lower or 3.0 or higher is in use and if the jQuery frameworks was already loaded to avoid incompatibility issues -->
	<script src="/mit/templates/goodkarma/js/jquery.flexslider-min.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	
	<? include("Analytics.php"); ?>
	<script>
		analytics.track('Edited Profile', {
		
		});		
	</script>
	<?php
        if ( stripos($delims,'"0"') === false ){
            echo "<script>var delimiters = {'Schools':{},'Departments':{}};var univData=JSON.parse('".json_encode($univData)."');</script>";
        }
        else {
            echo "<script>var delimiters = JSON.parse('".$delims."');var univData=JSON.parse('".json_encode($univData)."');</script>";
        }
        echo "<script>var departmentList = JSON.parse('".$dept."');</script>";
	?>
	</body>
</html>
