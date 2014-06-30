<?php
include("../algorithm.php");
session_start();

/*if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true))
  header("Location: http://www.projectlever.com/webfiles/login/login/");
if($_SESSION['university']=="EdX")
  header("Location: http://www.projectlever.com/EdX/profile.php");
if ( $_SESSION["university"]=="UKTI" ){
  header("Location: http://www.projectlever.com/UKTI/profile.php");
}*/

// Define some variables
$categories = array("Advisor","Course","Thesis","Grant");
$_SESSION['email']='demodiv';

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
if(mysqli_connect_errno($con))
  echo "Failed to connect to MySQL: " . mysqli_connect_error($con);

// Get user data
$sql = "SELECT *
			FROM Users
			WHERE Email='$_SESSION[email]'";

$result = mysqli_query($con, $sql);
$row = mysqli_fetch_array($result);

$name                   = $row["Name"];
$school                 = $row["School"];
$department             = $row["Department"];
$focus                  = $row["Focus"];	
$interests              = $row["Interests"];
$university             = $_SESSION['university'];
$profileImage           = $row["Profile_Image"];
$outline                = $row["Outlines"];
$_SESSION["delimiters"] = $row["Delimiters"];
$session_id = session_id();
echo "<script>var session_id = '".$session_id."';</script>";

$_SESSION['class'] = $row['Class'];
$_SESSION['school'] = $school;
$_SESSION['department'] = $department;

// Get saved resources
$saved = array();
for ($i = 0; $i < count($categories); $i++){
  $saved[$categories[$i]] = array();
}
$sql = "SELECT *
			FROM `Saved`
			WHERE Email = '$_SESSION[email]'";
if (!$result = mysqli_query($con,$sql))
  echo mysqli_error($con);
else{
  while($row = mysqli_fetch_array($result)){
    $saved[ucfirst($row["Type"])][$row["Item_ID"]] = "";
  }					
}
mysqli_close($con);

// Get university Data				
$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
if(mysqli_connect_errno($con))
  echo "Failed to connect to MySQL: " . mysqli_connect_error($con);

// Get division data
$sql = "SELECT *
		FROM `Advisor`
		WHERE `Department` = '".json_encode($department,true)."'";
if (!$result = mysqli_query($con, $sql))
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

// Get saved resources
foreach($saved as $type=>$ids){
  if ( $type != "" ){								
		     $sql = "SELECT `".$type."_ID`,`Name`
				FROM `".$type."`
				WHERE `University` = '".$university."'";	
		     
		     if(!$result = mysqli_query($con,$sql))
		       echo mysqli_error($con);
		     else{
    while($row = mysqli_fetch_array($result)){
      if($saved[$type]){
	if(array_key_exists($row[$type."_ID"],$saved[$type])){
	  $saved[$type][$row[$type."_ID"]] = $row["Name"];
	}
      }
    }
  }
		     }
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
	if($_SESSION["department"] == "Government" && $university == "Harvard_University"){
		$browse = "http://infomous.com/embed?nid=47789&interface=viewer&onsite=true&width=100%&height=100%";
	}

    mysqli_close($con);
?>
