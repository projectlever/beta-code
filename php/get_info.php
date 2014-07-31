<?php
session_start();
$autoRun = false; // Prevents find_similar_resources in get_similar.php from running automatically
 
include('/home/svetlana/www/beta-code/backend/sqlSearch/sqlNameSearch.php');

define("_ROOT_","/home/svetlana/www/");
if ( isset($_POST["id"]) ){
  $id = $_POST['id'];
  $type = $_POST['type'];
  echo get_info($id,$type);
}

function get_info($id,$type){
  $fields = array(
    "Advisor"=>array("Name","School","Department","University","Header","Link","Email","Picture","Info","Block","Advisor_ID"),
    "Course"=>array("Name","School","Department","University","Faculty","Description","Course_ID"),
    "Funding"=>array("FirstNamePI","Co-PINames","University","Name","Abstract","Email","Link","Funding_ID"),
    "Thesis"=>array("Author","Department","School","University","Abstract","Advisor1","Name","Thesis_ID"),
    "Grant"=>array("Name","University","Description","Sponsor","Email","Link","Grant_ID")
  );
  if ( isset($fields[$type]) ){

    // Get the prestored session data. This contains information on the latest match results
    // The weights (stored in the session data) are necessary for speeding up the matching algorithm.

    if ($_SESSION["data"]){
      $json = $_SESSION["data"];
      $data = $json[$type];
      for($i = 0; $i < count($data); $i++){
	if($data[$i]["id"] == $id){
	  $weights = $data[$i]['weights'];
	  $total = 0;
	  foreach($weights as $x=>$x_value){
	    if($total <= $x_value)
	      $total = $x_value;
	  }
	  foreach($weights as $x => $x_value){
	    $weights[$x] = round($x_value/$total * 100, 2) / 100;
	    arsort($weights);
	  }
	  break;
	}
      }
    }
    
    // Set up the return variable
    $out = array($type=>array(),"weights"=>$weights);
    foreach ($fields as $res_type=>$field_arr){
      if ( $res_type != $type )
	$out[$res_type] = array();
    }
    
    // Find the advisor visualization
    if ( $type == "Advisor" )
      $out["vizDataExists"] = file_exists(_ROOT_."advisor_viz/".$id.".json");
    else
      $out["vizDataExists"] = false;

    // Connect to the database and get all of the information for each $type of resource

    $con = sql_connect('svetlana_Total');
    $result = sql_query($con,"SELECT * FROM `".$type."` WHERE `".$type."_ID`=".$id);
    
    while ( $row = mysqli_fetch_array($result) ){
      foreach ( $fields[$type] as $index=>$fieldName ){

	// Check for JSON format - a lot of fields are stored as JSON, but some are not. So we should double check for that
	$data = json_decode($row[$fieldName],true);
	if ( $data == null ) // Not JSON format
	  $data = $row[$fieldName];	  
	
	$out[$type][$fieldName] = $data;
      }
      $out[$type]["type"] = $type;

      // If we are not looking at an Advisor or a Grant, then we want to find other resources of the same $type that are related to the advisor
      // So, if an advisor has taught multiple courses, the following code finds those other courses :)

      if ( $type != "Advisor" && $type != "Grant" ){

	// Get the name or names of the advisor(s) who are related to this $type of resource
	if ( $type == "Course" )
	  $names = explode(",",($row["Faculty"]));
	else if ( $type == "Funding" ){
	  $names = array_merge(explode(",",$row["Co-PINames"]),array($row["FirstNamePI"]));
	}
	else if ( $type == "Thesis" ){
	  $names = explode(",",($row["Advisor1"]));
	}

	// Some times there are multiple names in the field. So, we will take those names and place them in an array for easier processing
	foreach ($names as $index=>$name){
	  if ( stripos($name,"and") !== FALSE ){
	    unset($names[$index]);
	    if ( substr_count($name,",") > 1 ){
	      $names = array_merge($names,explode(",",str_replace("and","",$name)));	    
	    }
	    else {
	      $temp = $name;
	      $names = array_merge($names,explode("and",$temp));
	    }
	  }
	}

	// For every name, run a name matching algorithm on all of the resources in the database
	foreach ($names as $index=>$name){
	  if ( $name != "" ){
	    $name = str_replace(",","",$name);
	    $matches = name_search($name,"Advisor",array(
	      "options"=>array(
		"match"=>true
	      ),
	      "fields"=>array(
		"University"=>$row["University"]
	      )
	    ),"Name");

	    // For each name match, get that advisor's information!
	    for ( $i = 0, $n = count($matches); $i < $n; $i++ ){
	      $out["Advisor"][] = array();
	      $iii = count($out["Advisor"])-1;
	      $res = sql_query($con,"SELECT `Advisor_ID`,`Name`,`Header`,`Block`,`Picture` FROM `Advisor` WHERE `Advisor_ID`=".$matches[$i]);
	      while ( $_row = mysqli_fetch_array($res) ){
		$out["Advisor"][$iii]["Name"] = $_row["Name"];
		$out["Advisor"][$iii]["Id"] = $_row["Advisor_ID"];
		if ( strlen($_row["Header"]) > 5 )		
		  $out["Advisor"][$iii]["Description"] = $_row["Header"];
		else
		  $out["Advisor"][$iii]["Description"] = substr(strip_tags($_row["Block"]),0,350);
		$out["Advisor"][$iii]["Link"] = "single_display.php?id=".$_row["Advisor_ID"]."&type=Advisor";
		$out["Advisor"][$iii]["Picture"] = $_row["Picture"];
		$out["Advisor"][$iii]["type"] = "Advisor";
	      }
	    }
	  }
	}
	// Put the new information into the "similar" array
	$out["similar"] = array();
	foreach ($out["Advisor"] as $index=>$advisor){
	  $out["similar"][] = getAdvisorResources($con,$advisor);
	}
      }
      else if ( $type == "Advisor" ){
	$out = array_merge($out,getAdvisorResources($con,$row));
      }
      break;  
    }
    mysqli_close($con);
    return json_encode($out);
  }
}
function getAdvisorResources($con,$row){
  $out = array();
  $matches = name_search($row["Name"],"Course",array(
    "options"=>array(
      "match"=>true
    ),
    "fields"=>array(
      "University"=>$row["University"]
    )
  ),"Faculty");
  for ( $i = 0, $n = count($matches); $i < $n; $i++ ){
    $out["Course"][] = array();
    $iii = count($out["Course"])-1;
    $res = sql_query($con,"SELECT * FROM `Course` WHERE `Course_ID`=".$matches[$i]);
    while ( $_row = mysqli_fetch_array($res) ){
      $out["Course"][$iii]["Name"] = $_row["Name"];
      $out["Course"][$iii]["Description"] = $_row["Description"];
      $out["Course"][$iii]["Link"] = "single_display.php?id=".$matches[$iii]."&type=Course";
      $out["Course"][$iii]["Id"] = $_row["Course_ID"];
      $out["Course"][$iii]["type"] = "Course";
    }
  }
  $matches = name_search($row["Name"],"Thesis",array(
    "options"=>array(
      "match"=>true
    ),
    "fields"=>array(
      "University"=>$row["University"]
    )
  ),"Advisor1");
  for ( $i = 0, $n = count($matches); $i < $n; $i++ ){
    $out["Thesis"][] = array();
    $iii = count($out["Thesis"])-1;
    $res = sql_query($con,"SELECT * FROM `Thesis` WHERE `Thesis_ID`=".$matches[$i]);
    while ( $_row = mysqli_fetch_array($res) ){
      $out["Thesis"][$iii]["Name"] = $_row["Name"];
      $out["Thesis"][$iii]["Description"] = $_row["Abstract"];
      $out["Thesis"][$iii]["Link"] = "single_display.php?id=".$matches[$iii]."&type=Thesis";
      $out["Thesis"][$iii]["Author"] = $_row["Author"];
      $out["Thesis"][$iii]["Id"] = $_row["Thesis_ID"];
      $out["Thesis"][$iii]["type"] = "Thesis";
    }
  }
  $matches = name_search($row["Name"],"Funding",array(
    "options"=>array(
      "match"=>true
    ),
    "fields"=>array(
      "University"=>$row["University"]
    )
  ),"FirstNamePI");
  for ( $i = 0, $n = count($matches); $i < $n; $i++ ){
    $out["Funding"][] = array();
    $iii = count($out["Funding"])-1;
    $res = sql_query($con,"SELECT * FROM `Funding` WHERE `Funding_ID`=".$matches[$i]);
    while ( $_row = mysqli_fetch_array($res) ){
      $out["Funding"][$iii]["Name"] = $_row["Name"];
      $out["Funding"][$iii]["Description"] = $_row["Abstract"];
      $out["Funding"][$iii]["Link"] = "single_display.php?id=".$matches[$iii]."&type=Funding";
      $out["Funding"][$iii]["coPiNames"] = $_row["Co-PINames"];
      $out["Funding"][$iii]["Id"] = $_row["Funding_ID"];
      $out["Funding"][$iii]["type"] = "Grant";
    }
  }
  return $out;
}
?>
