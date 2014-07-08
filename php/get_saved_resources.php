<?php 
session_start();
if ( !(isset($_SESSION['loggedin']) && $_SESSION["loggedin"] == true )){
  echo "Not logged in";
  exit;
}

/******** Helper Function ***********/
function alphaExists($string){
  preg_match("/\S/",$string,$chars);
  if ( count($chars) > 0 )
    return TRUE;
  else
    return FALSE;
}

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
if(mysqli_connect_errno($con))
  die("Failed to connect to MySQL: " . mysqli_connect_error($con));
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
else {
  while($row = mysqli_fetch_array($result)){
    if ( alphaExists($row["Type"]) ){
      if ( !isset($saved[$row["Type"]]) )
	$saved[$row["Type"]] = array();
      if ( alphaExists($row["Item_ID"]) )
	$saved[$row["Type"]][] = $row["Item_ID"];
    }
  }
  echo json_encode($saved);
}
mysqli_close($con);
?>
