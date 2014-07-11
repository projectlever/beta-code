<?php

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
if (mysqli_connect_errno($con)){
  die(mysqli_error($con));
}
// Find all advisors with all resources
$sql = "SELECT `Advisor_ID`,`Name` FROM `Advisor` WHERE `Block` != ''";
$result = mysqli_query($con,$sql);
if ( !$result )
  die(mysqli_error($con));
else {
  while ( $row = mysqli_fetch_array($result) ){
    $sql2 = "SELECT `Course_ID` FROM `Course` WHERE `Faculty` LIKE '%".mysqli_real_escape_string($con,$row['Name'])."%'";
    $res2 = mysqli_query($con,$sql2);
    if ( !$res2 )
      die(mysqli_error($con));
    else {
      if ( mysqli_num_rows($res2) > 0 ){
	$sql3 = "SELECT `Thesis_ID` FROM `Thesis` WHERE `Advisor1` LIKE '%".mysqli_real_escape_string($con,$row["Name"])."%'";
	$res3 = mysqli_query($con,$sql3);
	if ( !$res3 )
	  die(mysqli_error($con));
	else {
	  if ( mysqli_num_rows($res3) )
	    echo $row["Advisor_ID"]."<br/>";
	}
      }
    }
  }
}
mysqli_close($con);

?>
