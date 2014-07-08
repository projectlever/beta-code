<?php
echo "Working...";
// Set error logging
error_reporting(-1);
ini_set('display_errors','On');

$uni = $_GET["university"];
$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
if ( !$con )
   echo mysqli_error($con);
else {
     $sql = "SELECT `Crest` FROM `Browse` WHERE `University`='$uni'";
     $result = mysqli_query($con,$sql);
     if ( !$result )
     	echo mysqli_error($con);
     else {
     	  while ( $row = mysqli_fetch_array($con) ){
	  	echo $row["Crest"];
		break;
	  }
     }
}
mysqli_close($con);
echo "Done";
?>