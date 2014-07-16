<?php
session_start();
 
include('/home/svetlana/www/beta-code/backend/sqlSearch/sqlNameSearch.php');

define("_ROOT_","/home/svetlana/www/");
$id = $_POST['id'];
$type = $_POST['type'];

$fields = array(
  "Advisor"=>array("Name","School","Department","University","Header","Link","Email","Picture","Info","Block"),
  "Course"=>array("Name","School","Department","University","Faculty","Description"),
  "Funding"=>array("FirstNamePI","Co-PINames","University","Name","Abstract","Email","Link"),
  "Thesis"=>array("Author","Department","School","University","Abstract","Advisor1","Name")
);
if ( isset($fields[$type]) ){
  $con = sql_connect('svetlana_Total');
  $result = sql_query($con,"SELECT * FROM `".$type."` WHERE `".$type."_ID`=".$id);
  
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

  // Find the advisor viz
  if ( $type == "Advisor" )
    $out["vizDataExists"] = file_exists(_ROOT_."advisor_viz/".$id.".json");
  else
    $out["vizDataExists"] = false;
  
  while ( $row = mysqli_fetch_array($result) ){
    foreach ($fields[$type] as $index=>$fieldName){
      // Check for JSON format
      $data = json_decode($row[$fieldName],true);
      if ( $data == null ) // Not JSON format
	$data = $row[$fieldName];
      
      $out[$type][$fieldName] = $data;

    }
    if ( $type != "Advisor" ){
      if ( $type == "Course" )
	$names = explode(",",($row["Faculty"]));
      else if ( $type == "Funding" ){
	$names = array_merge(explode(",",$row["Co-PINames"]),array($row["FirstNamePI"]));
      }
      else if ( $type == "Thesis" ){
	$names = explode(",",($row["Advisor1"]));
      }
      foreach ($names as $index=>$name){
	if ( stripos($name,"and") !== FALSE ){
	  $temp = $name;
	  unset($names[$index]);
	  $names = array_merge($names,explode("and",$temp));
	}
      }
      foreach ($names as $index=>$name){
	if ( $name != "" ){
	  $matches = name_search($name,"Advisor",array(
	    "options"=>array(
	      "match"=>true
	    ),
	    "fields"=>array(
	      "University"=>$row["University"]
	    )
	  ),"Name");
	  for ( $i = 0, $n = count($matches); $i < $n; $i++ ){
	    $out["Advisor"][] = array();
	    $iii = count($out["Advisor"])-1;
	    $res = sql_query($con,"SELECT `Name`,`Header`,`Block`,`Picture` FROM `Advisor` WHERE `Advisor_ID`=".$matches[$i]);
	    while ( $_row = mysqli_fetch_array($res) ){
	      $out["Advisor"][$iii]["Name"] = $_row["Name"];
	      if ( strlen($_row["Header"]) > 5 )		
		$out["Advisor"][$iii]["Description"] = $_row["Header"];
	      else
		$out["Advisor"][$iii]["Description"] = substr(strip_tags($_row["Block"]),0,350);
	      $out["Advisor"][$iii]["Link"] = "single_display.php?id=".$matches[$iii]."&type=Advisor";
	      $out["Advisor"][$iii]["Picture"] = $_row["Picture"];
	    }
	  }
	}
      }
    }
    else if ( $type == "Advisor" ){
      // Get all the information!
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
	  $out["Funding"][$iii]["Link"] = "single_display.php?id=".$matches[$iii]."&type=Grant";
	  $out["Funding"][$iii]["coPiNames"] = $_row["Co-PINames"];
	}
      }
    }
    echo json_encode($out);
    break;  
  }
  mysqli_close($con);
}
?>
