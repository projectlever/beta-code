<?php 
require($config["API_PATH"]."libs/algorithm.php");
if ( !function_exists("sql_connect") )
  include($config["API_PATH"]."sqlConfig.php");

function match($input){
  $con = sql_connect("svetlana_Total");
  // Set up categories
  $categories = array(
    "Advisor" => array("Advisor_ID","Tags","Picture","Department","School","Name","Header","Email"),
    "Course" => array("Course_ID","Tags","School","Department","Name","Description"),
    "Thesis" => array("Thesis_ID","Tags","School","Department","Name","Author","Abstract"),
    "Grant" => array("Grant_ID","Tags","Name","Description"),
    "Funding" => array("Funding_ID","Tags","Name","Abstract","University","FirstNamePI")
  );
  
  // Set up json
  $json = array(
    "Advisor"=>array(),
    "Course"=>array(),
    "Thesis"=>array(),
    "Grant"=>array(),
    "Funding"=>array()
  );

  // Prep the input!
  $input = simplePrep($input);
   
  // Let the insanity commense!

  // Loop through each category, comparing the prepped input to the category's blob
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
	    "rank" => $rank
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

  mysqli_close($con);
  return $json;
}
?>
