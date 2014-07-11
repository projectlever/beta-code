<? 
    session_start();
	if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true))
		header("Location: http://www.projectlever.com/webfiles/login/login/");

    $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
	if (mysqli_connect_errno($con))
		echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
	
	// Get user's department
	$sql = "SELECT `Department`
			FROM `Users`
			WHERE `Email` = '$_SESSION[email]'";
	if(!$result = mysqli_query($con,$sql))
		echo mysqli_error($con);
	else{
		$row = mysqli_fetch_array($result);
		$userDpt = $row['Department'];
	}
	
	$sql = "SELECT *
			FROM Saved
			WHERE Email='$_SESSION[email]'
			AND Type='advisor'
			AND Item_ID='$_GET[id]'";
	if(!$result = mysqli_query($con,$sql))
		echo mysqli_error($con);
	else{
		if(mysqli_num_rows($result) == 0)
			$saved = "Save";
		else
			$saved = "Remove";
	}
	$sql = "SELECT *
			FROM `History`
			WHERE `Item_ID`='$_GET[id]'
			AND `Type`='advisor'
			AND `Email`='$_SESSION[email]'";
	if(!$result = mysqli_query($con,$sql))
		echo "Select Error: ".mysqli_error($con);
	else{
		if(mysqli_num_rows($result) == 0){
			$sql = "INSERT INTO `History`
					(`Email`,`Item_ID`,`Type`)
					VALUES ('".$_SESSION["email"]."','".$_GET["id"]."','advisor')";
			if(!$result = mysqli_query($con,$sql))
				echo "Insert Error: ".mysqli_error($con);
		}
		else{
			$row = mysqli_fetch_array($result);
			$sql = "UPDATE `History`
					SET `Time` = NOW()
					WHERE `History_ID` = ".$row['History_ID'];
			if(!mysqli_query($con,$sql))
				echo "Update Error: ".mysqli_error($con);
		}	
	}
	mysqli_close($con);
	if ($_SESSION["data"]){
		$json = $_SESSION['data'];
		$data = $json['Advisor'];
		for($i = 0; $i < count($data); $i++){
			if($data[$i]["id"] == $_GET["id"]){
				$weights = $data[$i]['weights'];
				$total = 0;
				foreach($weights as $x=>$x_value){
					if($total <= $x_value)
						$total = $x_value;
				}
				foreach($weights as $x => $x_value){
					$weights[$x] = round($x_value/$total * 100, 2) / 100;
					arsort($weights);
				}
				break;
			}
		}
	}
	echo "<script>var id = ".$_GET['id'].";</script>";
	echo "<script>var weights = ".json_encode($weights).";</script>";

?>
<!DOCTYPE html>
<html xml:lang="en-gb" lang="en-gb">
	<head> 
		<base href="http://projectlever.com/profile.php"/>
		<meta name="keywords" content="mentorship, collaboration, academia, guide, advisor, writing, library"/>
		<meta name="rights" content="Project Lever LLC"/>
		<meta name="description" content="Online platform for collaboration in universities."/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >

		<title>Advisor</title>
		
		<!-- Le scripts -->
		
		<script src="media/system/js/mootools-core.js" type="text/javascript"></script>
		<script src="media/system/js/core.js" type="text/javascript"></script>
		<script src="media/system/js/caption.js" type="text/javascript"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>  	
		<script src="http://d3js.org/d3.v3.min.js"></script>
		<script src="templates/goodkarma/js/bootstrap.min.js" type="text/javascript"></script>
   		<script src="beta-code/js/single_advisor_display.js" type="text/javascript"></script>
   		
		<!-- Le styles -->
  
		<link href="http://projectlever.com/favicon.ico" rel="shortcut icon"/>
		<link href="plugins/content/xtypo/themes/default/style.css" rel="stylesheet" type="text/css" />
		<link href="templates/goodkarma/css/bootstrap.css" rel="stylesheet"/>
		<link href="templates/goodkarma/css/bootstrap-responsive.css" rel="stylesheet"/>
		<link href="templates/goodkarma/css/bootstrap_extend.css" rel="stylesheet"/>
		<link href="templates/goodkarma/css/flexslider.css" rel="stylesheet"/>
		<link href="templates/goodkarma/css/style.css" rel="stylesheet"/>
		<link href="beta-code/css/single_display.css" rel="stylesheet"/>
	
		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<!-- Le fav and touch icons -->
		
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-144-precomposed.png"/>
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-114-precomposed.png"/>
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-72-precomposed.png"/>
		<link rel="apple-touch-icon-precomposed" href="http://twitter.github.com/bootstrap/assets/ico/apple-touch-icon-57-precomposed.png"/>
  
	</head>
	<!--- BAROMETER FEEDBACK SCRIPT --->
        <style type='text/css'>@import url('http://getbarometer.s3.amazonaws.com/assets/barometer/css/barometer.css');</style>
        <script src='http://getbarometer.s3.amazonaws.com/assets/barometer/javascripts/barometer.js' type='text/javascript'></script>
        <script type="text/javascript" charset="utf-8">
           BAROMETER.load('bHQAerFMj8fLB3GQTcm1W');
           $ = jQuery;
           $("#barometer_tab").attr("href","javascript:void(0);");
        </script>
        <!--- END BAROMETER FEEDBACK SCRIPT --->
	<body onload="d3load()">
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
											<li class="item-237"><a href="/profile.php" style="color:#dddddd">Profile</a></li>
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
						<?
							$university = $_SESSION['university'];
							$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
							if (mysqli_connect_errno($con))
								echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
							$id = $_GET["id"];
							
							if(!$result = mysqli_query($con,"SELECT * FROM `DUS` WHERE `University` = '$_SESSION[university]' AND `Department` = '".$userDpt."'"))
								echo mysqli_error($con);
							$row = mysqli_fetch_array($result);
							$DUSemail = $row['Email'];

							if(!$result = mysqli_query($con,"SELECT * FROM `Advisor` WHERE Advisor_ID='".$id."'"))
								echo mysqli_error($con);
							$row = mysqli_fetch_array($result);
						?>
						<div class="row-fluid">
							<div class="span0 moduletable module-column mod-204 span2 no-title clearfix">	
								<div class="module-block">	
									<div class="module-header">
										<h3 class="module-title"><span>Advisor</span> Picture</h3>
									</div>			
									<div class="module-content">
										<div class="custom" style="font-size:14px"> 
											<?
												if($row["Picture"])
													echo '<img src="'.$row["Picture"].'" alt="/images/LittleAdvisor.png" width="150px" border-radius="15px" />';
												else
													echo '<img src="/images/LittleAdvisor.png" alt="" width="150px" border-radius="15px" />';
											?>
										</div>
										<div class="clear">
										</div>
									</div>
								</div>	
							</div>
							<div class="span0 moduletable module-column mod-199 span8 no-title clearfix">
								<div class="module-block">
									<div class="module-header">
										<h3 class="module-title"><span>Advisor</span> Title</h3>
									</div>
									<div class="module-content">
										<div class="custom">
											<h3 style="color:#dddddd">
												<?
													echo $row["Name"];
												?>
											</h3>
											<h4 style="color:#dddddd" class="title">
												<? 
													echo $row["Header"];
												?>
											</h4>
											<hr/ style="border-bottom:0px">
											<h4>
												<?
													$school = json_decode($row["School"],true);
													$department = json_decode($row["Department"],true);
													if ( $department != null && $department != false){
														if(is_array($department))
															$department = implode(" - ",$department);
														else
															$department = json_decode($row["Department"],true);
													}
													else{
														$department = $row["Department"];
													}
													if ( $school != null && $school != false ){
														if(is_array($school))
															$school = implode(" - ",$school);
														else
															$school = json_decode($row["School"],true);
											
													}
													else {
														$school = $row["School"];
													}
													echo str_replace("_"," ",$row["University"])." | ".$school." | ". $department;
													
													if($DUSemail != '')
														$DUS = '<a href="mailto:'.$DUSemail.'">Director of Undergraduate Studies</a>';
													else
														$DUS = 'Director of Undergraduate Studies';
												?>
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
											
											<input type="hidden" id="professor_id" name="id" value=<? echo '"'.$_GET['id'].'"' ?> />
											<input type="hidden" id="type" name="type" value="advisor" />
											<input type="hidden" name="saved" value=<? echo '"'.$saved.'"'; ?> />
											<button id="save_remove_button" onclick="saveProfessor()" class="bigButton" style="margin-top:10px"><? echo $saved; ?></button>
											
											<script type="text/javascript">
												var form = $('.save');
												if ($("[name='saved']").val()=="Save"){
													analytics.trackForm(form, 'Saved Advisor', {
														advisorId : $("[name='id']").val(),
														advisorName : $(".title").html(),
														advisorDepartment : $(".department").html()
													});
												}
												else {
													analytics.trackForm(form, 'Removed Advisor', {
														advisorId : $("[name='id']").val(),
														advisorName : $(".title").html(),
														advisorDepartment : $(".department").html()
													});
												}
											</script>
											<button class="bigButton" style="margin-top:10px" onclick="contact();">Contact</button>
											<?
												$emails = json_decode($row["Email"],true);
												echo "<div class='emailBox'><div class='X'>X</div>";
												if($DUSemail != '')
													echo "<div style='max-width:210px'>Please see your DUS (email: ".$DUSemail.") before contacting this professor</div>";
												for($i = 0, $n = count($emails);$i < $n; $i++){
													echo "<div class='emailDiv'><a href='mailto:".$emails[$i]."'>".$emails[$i]."</a></div>";
												}
												if(count($emails) == 0){
													echo "<div class='emailDiv'>Sorry! We couldn't locate this advisor's email, but it might still be here on their profile page. Look around or check out the department's website.</div>";
												}
												echo "</div>";												
											?>
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
								<div class="module-block" id="about_block">
									<? if($row["Block"] != ''): ?>
									<div class="module-header">
										<h3 class="module-title"><span>About</span></h3>
									</div>
									<? endif; ?>
									<div class="module-content">
										<div class="custom"  >  
											<?
												echo $row["Block"] != "" ? str_ireplace("<img","<img onerror='$(this).remove()'",$row["Block"]) : "<div id='prelim'>This advisor's profile is under construction.</div>";
												if($weights){	
													echo "<div id='tags'>Tags:";
													foreach($weights as $x=>$x_value){
														echo "<span style='opacity:".$x_value."'>".$x."</span>";
													}
													echo "</div>";
												}
											?>
											<div id="viz">
											</div>	
										</div>
										<div class="clear">
										</div>
									</div>
								</div>
							</div>
							<div class="span0 moduletable module-column mod-203 span4 clearfix">
								<div class="module-block">
									<? if($row["Info"] != ''): ?>
									<div class="module-header">
										<h3 class="module-title"><span>Info</span></h3>
									</div>
									<? endif; ?>
									<div class="module-content">
										<div class="custom">
											<?       
												if(is_array(json_decode($row["Link"],true)))
													$link = $row["Link"][0];
												else
													$link = json_decode($row["Link"],true);
												if($row["Scraped_Level"] == "Secondary")
													echo $row["Info"];
												elseif($link != '""'){}
												else
													echo "<button class='bigButton' style='margin-left:53%' onclick=".'"'."window.open('".$link."');".'"'.">See More</button>";
												$unicon = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
												if (mysqli_connect_errno($con))
													echo "Failed to connect to MySQL: " . mysqli_connect_error($con);

												$profCourses = array();
												$profTheses = array();
												$sql = "SELECT *
														FROM Advisor
														WHERE Advisor_ID = '$id'";
												$result = mysqli_query($con,$sql);
												if(!$result)
													mysqli_error($con);
												else{
													while($row = mysqli_fetch_array($result)){
														$z = $row["Name"];
													}
												}
												$sql = "SELECT *
														FROM Course";
												$result = mysqli_query($unicon,$sql);
												if(!$result)
													echo mysqli_error($unicon);
												else{
													while($row = mysqli_fetch_array($result)){
														$courseText = $row["Name"]." ".$row["Description"]." ".$row["Faculty"];
														if((preg_match("/".$x."/",$courseText) && preg_match("/".$y."/",$courseText) && $x != "" && $y != "") || preg_match("/".$z."/",$courseText)){
															array_push($profCourses,$row["Course_ID"]);
														}
													}
												}
												$sql = "SELECT *
														FROM Thesis";
												$result = mysqli_query($unicon,$sql);
												if(!$result)
													mysqli_error($unicon);
												else{
													while($row = mysqli_fetch_array($result)){
														$thesisText = $row["Name"]." ".$row["Abstract"]." ".$row["Advisor1"]." ".$row["Advisor2"]." ".$row["Advisor3"];
														if((preg_match("/".$x."/",$thesisText) && preg_match("/".$y."/",$thesisText) && $x != "" && $y != "") || preg_match("/".$z."/",$thesisText)){
															array_push($profTheses,$row["Thesis_ID"]);
														}
													}
												}
												if($profCourses){
													echo "<h4>Courses</h4>";
													echo "<ul>";
													for($i = 0, $n = count($profCourses); $i < $n; $i++){
														$sql = "SELECT *
																FROM Course
																WHERE Course_ID = $profCourses[$i]";
														$result = mysqli_query($con,$sql);
														if(!$result){
															echo "test";
															echo mysqli_error($con);
														}
														else{
															while($row = mysqli_fetch_array($result)){
																echo "<li><a href='single_course_display.php?id=".$row["Course_ID"]."'>".$row["Name"].": </a><br/>".substr($row["Description"],0,100)."</li>";
															}
														}
													}
													echo "</ul>";
												}
												if($profTheses){
													echo "<h4>Student Projects</h4>";
													echo "<ul>";
													for($i = 0, $n = count($profTheses); $i < $n; $i++){
														$sql = "SELECT *
																FROM Thesis
																WHERE Thesis_ID = $profTheses[$i]";
														$result = mysqli_query($con,$sql);
														if(!$result){
															echo mysqli_error($con);
														}
														else{
															while($row = mysqli_fetch_array($result)){
																echo "<li><a href='single_thesis_display.php?id=".$row["Thesis_ID"]."'>".$row["Name"].": </a>".substr($row["Abstract"],0,100)."</li>";
															}
														}
													}
													echo "</ul>";
												}
											?>
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

			<!-- ##################################################### TOP AREA ENDS HERE ##################################################### -->

			<!-- ************************ MAIN AREA START************************ -->
						
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
	
		<script src="/templates/goodkarma/js/jquery.flexslider-min.js"></script>
	
		<? include("../Analytics.php"); ?>
	
		<script type='text/javascript'>
			analytics.track('Viewed Advisor', {
				advisorId : $("[name='id']").val(),
				advisorName : $(".title").html(),
				advisorDepartment : $(".department").html()
			});		
			$("img").on("error",function(){
				$(this).remove();
			});
			$("img").each(function(){
				var img = $(this);
				if ( img.attr("src") == "" ){
					img.remove();
				}
			});
			$("#about_block").find("a").contents().unwrap();
			$("#about_block").find("hr").remove();
			$("#about_block").find("p,h1,h2,div").each(function() {
				var elem = $(this);
				if (elem.html() == "") {
				  elem.remove();
				}
			  }
			);
		</script>		
	</body>
</html>
