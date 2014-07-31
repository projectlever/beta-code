<?php
	
session_start();

$test = false;
if ( !(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true )){
  if ( !isset($_POST["test_drive"]) || $_POST["test_drive"] == false ){
    echo "Not logged in. " . session_id()." != ".$_POST["session"] . " test_drive=".$_POST["test_drive"];
    exit;
  }
  else {
    $test  = true;
    $class = 4;
  }
}
else {
  $class = $_SESSION['class'];
  $sessionUniversity = $_SESSION["university"];
  $sessionDepartment = json_encode($_SESSION["department"],true);
  $sessionDivision = $_SESSION['division'];
}

// Helps with encoding
header('Content-Type:text/html; charset=UTF-8');

// Useful algorithms
require("../../algorithm.php");

// Set up categories
$categories = array(
  "Advisor" => array("Advisor_ID","Tags","Picture","Department","School","Name","Header","Email"),
  "Course"  => array("Course_ID","Tags","School","Department","Name","Description"),
  "Thesis"  => array("Thesis_ID","Tags","School","Department","Name","Author","Abstract"),
  "Grant"   => array("Grant_ID","Tags","Name","Description"),
  "Funding" => array("Funding_ID","Tags","Name","Abstract")
);
// Set up json
$json = array(
  "Advisor"=>array(),
  "Course"=>array(),
  "Thesis"=>array(),
  "Grant"=>array(),
  "Funding"=>array()
);

$page = $_POST["page"];
$display = 10;

$input = $_POST["input"];

$input = simplePrep($input);

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
if (mysqli_connect_errno($con))
  echo "Failed to connect to MySQL: " . mysqli_connect_error($con);

switch($class){
  case 0:
  // See nothing
  echo "Not logged in";
  exit;
  break;
  case 1:
  // Only department
  $query = " WHERE `Name` != '' `Department` = '".$sessionDepartment."'";
  break;
  case 2:
  // Only division
  $query = " WHERE `Name` != '' `Division` = '".$sessionDivision."'";
  break;    		
  case 3:
  // Only undergraduate
  $query = " WHERE `Name` != '' `Class` = 'Undergraduate'";
  break;
  case 4:
  // See everything
  $query = " WHERE `Name` != ''";
  break;
  case 5:
  // Only graduate
  $query = " WHERE `Name` != '' `Class` = 'Graduate'";
  break;
}
if ( $test == false )
  $query .= " AND `University` = '".$sessionUniversity."'";

$dept_counter = array();
$univ_counter = array();
$univ_total = array();
$dept_total = array();

foreach($categories as $type => $value){
  $columns = implode(",",$value);
  $sql = "SELECT ".$columns." FROM `".$type."`".$query;
  
  if(!$result = mysqli_query($con,$sql))
    echo mysqli_error($con);
  else{
    while ($row = mysqli_fetch_array($result)){
      $rank = 0;
      $prof_weights = array();
      
      if ($row["Tags"] == "")
	continue;            
      else{            
	$processed = json_decode(utf8_encode($row["Tags"]),true);
	//print_r($processed);exit;
	
	// ID
	$id = $row[$type."_ID"];
	
	// Name
	$name = $row["Name"];
	
	// Rank and weights      
	for($i = 0, $n = count($input); $i < $n; $i++){
	  if (array_key_exists($input[$i],$processed)){
	    $prof_weights[$input[$i]] = $processed[$input[$i]];
	    $rank += $processed[$input[$i]];
	  }
	}
	
	// University
	$university = $row["University"];
	
	// School
	if ( json_decode($row["School"],true) != null && json_decode($row["School"],true) != false ){
	  if(is_array(json_decode($row["School"],true)))
	    $school = implode(" - ",json_decode($row["School"],true));
	  else
	    $school = json_decode($row["School"],true);
	}
	else {
	  $school = $row["School"];
	}
	
	// Department
	if ( json_decode($row["Department"],true) != null && json_decode($row["Department"],true) != false ){
	  if(is_array(json_decode($row["Department"],true)))
	    $department = implode(" - ",json_decode($row["Department"],true));
	  else
	    $department = json_decode($row["Department"],true);
	}
	else {
	  $department = $row["Department"];
	}
	
	// Description
	if($row["Header"]){
	  $head = trim(strip_tags(str_replace("\t","",str_replace("<br>"," | ",str_replace("<br/>"," | ", str_replace("<br />"," | ", $row["Header"]))))));
	  if(substr($head,0,1) == "|")
	    $head = substr($head,2);
	}
	elseif($row["Description"]){
	  $head = trim(strip_tags(str_replace("\t","",str_replace("<br>"," | ",str_replace("<br/>"," | ", str_replace("<br />"," | ", $row["Description"]))))));
	  if(strlen($head) >= 100)
	    $head = substr($head,0,100)."...";
	}
	elseif($row["Abstract"]){
	  $head = trim(strip_tags(str_replace("\t","",str_replace("<br>"," | ",str_replace("<br/>"," | ", str_replace("<br />"," | ", $row["Abstract"]))))));
	  if(strlen($head) >= 100)
	    $head = substr($head,0,100)."...";
	}
	$description = $head;
	
	// Picture
	if ($row["Picture"])
	  $picture = $row["Picture"];
	else
	  $picture = "/images/Little".$type.".png";
	
	// Author
	if($row["Author"]){
	  $author = $row["Author"];
	}
	else
	  $author = "";
	
	// Email
	if ( $row["Email"] )
	  $email = $row["Email"];
	else
	  $email = "";
	// Principle Investigator
	if($row["LastNamePI"]){
	  $pi = $row["FirstNamePI"]." ".$row["LastNamePI"];
	}
	elseif($row["FirstNamePI"]){
	  $pi = $row["FirstNamePI"];
	}
	else
	  $pi = "";

	// Count the department results!
	$dataArray = array(
	  "id" => $id,
	  "name" => $name,
	  "weights" => $prof_weights,
	  "rank" => $rank,
	  "school" => $school,
	  "department" => $department,
	  "description" => $description,
	  "picture" => $picture,
	  "author" => $author,
	  "pi" => $pi,
	  "email"=>$email,
	  "university"=>$university,
	  "type"=>$type
	);
	if($rank != 0)
	  array_push($json[$type],$dataArray);
      }
      // Reset variables
      $id = "";
      $name = "";
      $weights = array();
      $school = "";
      $department = "";
      $description = "";
      $picture = "";
      $author = "";
      $pi = "";
      $email = "";
      $university = "";
      $head = "";
    }  
  }
  
  // Sort it
  $ranks = array();
  foreach($json[$type] as $key => $value){
    $ranks[$key] = $value["rank"];
  }
  
  array_multisort($ranks, SORT_DESC, $json[$type]);
  
  $rankTotal = $json[$type][0]["rank"];
  
  for($i = 0, $n = count($json[$type]); $i < $n; $i++){
    if($rankTotal != 0){
      $json[$type][$i]["rank"] = round( ( $json[$type][$i]["rank"] / $rankTotal ) * 100 );
      if($json[$type][$i]["rank"] == 0)
	unset($json[$type][$i]);
      else {
	if ( $test == true ){
	  // Count universities!
	  // Count total results
	  if ( !$univ_total[$type] )
	    $univ_total[$type]=0;
	  $univ_total[$type]++;

	  // Count individual university results
	  $uname = $json[$type][$i]["university"];
	  echo $uname;
	  if ( !$univ_counter[$type] )
	    $univ_counter[$type] = array();
	  if ( !$univ_counter[$type][$uname] )
	    $univ_counter[$type][$uname] = 0;
	  $univ_counter[$type][$uname]++;
	}
	else {
	  // Count the departments!
	  // Count total results
	  if ( !$dept_total[$type] )
	    $dept_total[$type]=0;
	  $dept_total[$type]++;

	  // Count individual department results
	  if ( !$dept_counter[$type] )
	    $dept_counter[$type] = array();
	  if ( !$dept_counter[$type][$json[$type][$i]["department"]] )
	    $dept_counter[$type][$json[$type][$i]["department"]] = 0;
	  $dept_counter[$type][$json[$type][$i]["department"]]++;
	}
      }
    }
  }
}
$_SESSION["data"] = $json;
$desc = array("Advisor"=>"`Block`","Course"=>"`Description`","Grant"=>"`Description`","Thesis"=>"`Abstract`");
// Return only the amount given by $display and $page
$limitedJSON = array("results"=>array(),"result_count"=>array());
foreach ($json as $type=>$results){
  if ( !$limitedJSON["result_count"][$type] ){
    if ( $test == true )
      $limitedJSON["result_count"][$type] = $univ_counter[$type];
    else
      $limitedJSON["result_count"][$type] = $dept_counter[$type];
    if ( $test == true )
      $limitedJSON["result_count"][$type]["total"] = $univ_total[$type];
    else
      $limitedJSON["result_count"][$type]["total"] = $dept_total[$type];
  }
  for ($i = 0, $n = 10; $i < $n; $i++){
    if ( isset($results[$i]) ){
      if ( !$limitedJSON["results"][$type] )
	$limitedJSON["results"][$type] = array();
      $limitedJSON["results"][$type][$i] = $results[$i];
      $sql = "SELECT " . $desc[$type] . ", `University` FROM `$type` WHERE `".$type."_ID`=".$results[$i]["id"];
      $res = mysqli_query($con,$sql);
      if ( !$res )
	$limitedJSON["results"][$type][$i]["block"] = mysqli_error($con);
      else {
	while ( $row = mysqli_fetch_array($res) ){
	  $limitedJSON["results"][$type][$i]["block"] = $row[str_replace("`","",$desc[$type])];
	  if ( $test == true ){
	    $limitedJSON["results"][$type][$i]["university"] = $row["University"];
	  }
	}
      }
    }
  }
}

mysqli_close($con);
		
echo json_encode($limitedJSON);

//echo (microtime(true)-$startTime);
?>
