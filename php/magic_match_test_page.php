<?php
	
session_start();
if ( !(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true )){
  echo "Not logged in. " . session_id()." != ".$_POST["session"];
  exit;
}
// Helps with encoding
header('Content-Type:text/html; charset=UTF-8');

// Useful algorithms
require("../../algorithm.php");

// Set up categories
$categories = array(
  "Advisor" => array("Advisor_ID","Tags","Picture","Department","School","Name","Header","Email"),
  "Course" => array("Course_ID","Tags","School","Department","Name","Description"),
  "Thesis" => array("Thesis_ID","Tags","School","Department","Name","Author","Abstract"),
  "Grant" => array("Grant_ID","Tags","Name","Description"),
);
// Set up json
$json = array(
  "Advisor"=>array(),
  "Course"=>array(),
  "Thesis"=>array(),
  "Grant"=>array(),
);

$page = $_POST["page"];
$display = 10;
$class = $_SESSION['class'];
$sessionUniversity = $_SESSION["university"];
$sessionDepartment = json_encode($_SESSION["department"],true);
$sessionDivision = $_SESSION['division'];

//$input = "Russian and American poetry, particularly contemporary poetry; the film and poetry; feminist and psychoanalytic theories; Pushkin; comparative approaches to Russian literature; cultural studies.";
    $input = $_POST["input"];
    //$input = "Chemistry";

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
    		$query = " WHERE `Name` != '' AND `University` = '".$sessionUniversity."' AND `Department` = '".$sessionDepartment."'";
    		break;
    	case 2:
    		// Only division
    		$query = " WHERE `Name` != '' AND `University` = '".$sessionUniversity."' AND `Division` = '".$sessionDivision."'";
			break;    		
    	case 3:
    		// Only undergraduate
    		$query = " WHERE `Name` != '' AND `University` = '".$sessionUniversity."' AND `Class` = 'Undergraduate'";
    		break;
    	case 4:
    		// See everything
    		$query = " WHERE `Name` != '' AND `University` = '".$sessionUniversity."'";
    		break;
    	case 5:
    		// Only graduate
    		$query = " WHERE `Name` != '' AND `University` = '".$sessionUniversity."' AND `Class` = 'Graduate'";
    		break;
    }
    
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
	  "email"=>$email
	);
	if($rank != 0)
	  array_push($json[$type],$dataArray);
      }
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
    }
  }
}

$desc = array("Advisor"=>"`Block`","Course"=>"`Description`","Grant"=>"`Description`","Thesis"=>"`Abstract`");
// Return only the amount given by $display and $page
$limitedJSON = array();
foreach ($json as $type=>$results){
  for ($i = ($page-1), $n = ($page-1+10); $i < $n; $i++){
    if ( isset($results[$i]) )
      $limitedJSON[$type][] = $results[$i];
  }
}

mysqli_close($con);
		
$_SESSION["data"] = json_encode($json);
echo json_encode($limitedJSON);

//echo (microtime(true)-$startTime);
?>
