<?php 
// This script looks for Similar_Resource data in the database for a given id and type
require("/home/svetlana/www/beta-code/backend/lib.php");
$id   = $_POST["id"];
$type = $_POST["type"];

$con = sql_connect("svetlana_Total");
$ids = array();
while ($row = mysqli_fetch_array(sql_query($con,"SELECT `Similar_Resources` FROM `$type` WHERE `".$type."_ID`=".$id))){
  $ids = json_decode($row["Similar_Resources"],true);
  break;
};
if ( count($ids) == 0 )
  exit;
// Get the similar resource's information
$desc = array(
  "Advisor"=>"Header",
  "Course"=>"Description",
  "Funding"=>"Abstract",
  "Thesis"=>"Description"
);
$block = array(
  "Advisor"=>"Block",
  "Course"=>"Description",
  "Funding"=>"Abstract",
  "Thesis"=>"Description"
);
$name = array(
  "Advisor"=>"Name",
  "Course"=>"Name",
  "Funding"=>"Name",
  "Thesis"=>"Title"
);
$picture = array(
  "Course" => "/images/LittleCourseRed.png",
  "Funding"=> "/images/LittleGrantRed.png",
  "Thesis" => "/images/LittleThesisRed.png"
);
$out = array();
foreach ($ids[$type] as $index => $val){
  $row = mysqli_fetch_array(sql_query($con,"SELECT * FROM `$type` WHERE `".$type."_ID`=".$val["id"]));
  $temp = array(
    "description" => strip_tags($row[$desc[$type]]),
    "name"=>$row[$name[$type]],
    "id"=>$row[$type."_ID"]
  );
  if ( $type == "Advisor" ){
    $temp["email"]   = json_decode($row["Email"],true);
    if ( count($temp["email"]) > 0 )
      $temp["email"] = $temp["email"][0];
    if ( $temp["email"] == null )
      $temp["email"] = "";
    $temp["picture"] = $row["Picture"]; 
    $temp["block"]   = $row["Block"];
    $temp["department"] = implode(", ",json_decode($row["Department"],true));
    $temp["school"] = json_decode($row["School"],true);
  }
  else {
    $temp["picture"] = $picture[$type];
    $temp["block"]   = $row[$desc[$type]]; 
    $temp["department"] = $row["Department"];
    $temp["school"] = $row["School"];
  }
  $out[] = $temp;
} 
echo json_encode(array($type=>$out));
mysqli_close($con);
?>
