<?php 
// Get resource information
$id = $_POST["id"];
$type = $_POST["resource"];

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
if (mysqli_connect_errno($con))
  die("Failed to connect to MySQL: " . mysqli_connect_error($con));

if ( $type == "Advisor" ){
  $sql  = "SELECT `Block` FROM `Advisor` WHERE `Advisor_ID`=".$id;
  $desc = "Block";
}
else if ( $type == "Course" || $type == "Grant" ){
  $sql  = "SELECT `Description` FROM `$type` WHERE `".$type."_ID`=".$id;
  $desc = "Description";
}
else if ( $type == "Thesis" ){
  $sql  = "SELECT `Abstract` FROM `Thesis` WHERE `Thesis_ID`=".$id;
  $desc = "Abstract";
}
$result = mysqli_query($con,$sql);
if ( !$result )
  die(mysqli_error($con));
else {
  while ( $row = mysqli_fetch_array($result) ){
    echo $row[$desc];
  }
}
?>
