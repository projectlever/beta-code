<?php 
// This script looks for Similar_Resource data in the database for a given id and type
session_start();
require("/home/svetlana/www/beta-code/backend/lib.php");
require("/home/svetlana/www/beta-code/backend/match_algorithm.php");

if ( isset($_POST["id"]) ){
  $id   = $_POST["id"];
  $type = $_POST["type"];
  if ( $id != "" && $type != "" )
    echo find_similar_resources($id,$type);
}

function find_similar_resources($id,$type){
  $con = sql_connect("svetlana_Total");
  
  $blob = array(
    "Advisor"=>"Blob",
    "Course"=>"Description",
    "Funding"=>"Abstract",
    "Thesis"=>"Abstract"
  );
  
  // Get the advisor's blob
  $res = sql_query($con,"SELECT `".$blob[$type]."` FROM `$type` WHERE `".$type."_ID`=".$id);
  if ( mysqli_num_rows($res) == 0 )
    exit;
  $row = mysqli_fetch_array($res);
  
  // Run the matching algorithm
  $rank = match(sql_escape($con,$row[$blob[$type]]));
  // Remove the first one since it's the person we just searched. A resource always matches 100% to itself
  unset($rank[$type][0]); 
  // Limit the results to results of the same type
  $rank = $rank[$type];
  
  // Get the similar resource's information
  $desc = array(
    "Advisor"=>"Header",
    "Course"=>"Description",
    "Funding"=>"Abstract",
    "Thesis"=>"Abstract"
  );
  $block = array(
    "Advisor"=>"Block",
    "Course"=>"Description",
    "Funding"=>"Abstract",
    "Thesis"=>"Abstract"
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
  $count = 0;
  foreach ($rank as $index => $val){
    $res = sql_query($con,"SELECT * FROM `$type` WHERE `".$type."_ID`=".$val["id"]." AND `University`='".$_SESSION["university"]."'");
    if ( mysqli_num_rows($res) == 0 )
      continue;
    $row = mysqli_fetch_array($res);
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
    $count++;
    if ( $count == 20 ){
      break;  
    }
  } 
  mysqli_close($con);
  return json_encode(array($type=>$out));
}
?>
