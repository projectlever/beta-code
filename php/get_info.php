<?php

session_start();
 
include('/home/svetlana/www/beta-code/backend/sqlSearch/sqlNameSearch.php');
$id = $_POST['id'];
$type = $_POST['type'];

if ( $type == "Advisor" ){
  $con = sql_connect('svetlana_Total');
  $result = sql_query($con,"SELECT * FROM `".$type."` WHERE `".$type."_ID`=".$id);
  
  if ($_SESSION["data"]){
    $json = $_SESSION["data"];
    $data = $json["Advisor"];
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
  
  while ( $row = mysqli_fetch_array($result) ){
    $link = json_decode($row["Link"],true);
    if ( $link == null )
      $link = $row["Link"];
    $out = array("advisor"=>array(
      "name"=>$row["Name"],
      "school"=>json_decode($row["School"],true),
      "department"=>json_decode($row["Department"],true),
      "university"=>str_replace("_"," ",$row["University"]),
      "link"=>$link,
      "email"=>json_decode($row["Email"],true),
      "info"=>$row["Info"],
      "block"=>$row["Block"],
      "picture"=>$row["Picture"],
      "header"=>strip_tags($row["Header"])
    ),"Funding"=>array(),"Courses"=>array(),"Student Projects"=>array(),"weights"=>$weights);

    // Find all funding
    $fun_matches = name_search($row["Name"],"Funding",array("Email"=>$row["Email"],"University"=>$row["University"]),'FirstNamePI');
    // Loop through all of the funding IDs and get the information
    for ($it = 0, $no = count($fun_matches); $it < $no; $it++ ){
      $fun_res = mysqli_query($con,"SELECT * FROM `Funding` WHERE `Funding_ID`=".$fun_matches[$it]);
      if ( $fun_res ){
	while ( $fun_row = mysqli_fetch_array($fun_res) ){
	  $out["Funding"][] = array(
	    "name"=>$fun_row["Name"],
	    "description"=>$fun_row["Abstract"],
	    "coPiNames"=>$fun_row["Co-PINames"],
	    "id"=>$fun_row["Funding_ID"],
	    "link"=>"single_grant_display.php?id=".$fun_row["Funding_ID"]
	  );
	}
      }
    }

    // Find all courses
    $z = $out["advisor"]["name"];
    $course = "SELECT * FROM Course";
    $res = mysqli_query($con,$course);
    if(!$res)
      echo mysqli_error($con);
    else{
      while($_row = mysqli_fetch_array($res)){
	$courseText = $_row["Name"]." ".$_row["Description"]." ".$_row["Faculty"];
	if((preg_match("/".$x."/",$courseText) && preg_match("/".$y."/",$thesisText) && $x != "" && $y != "" ) || preg_match("/".$z."/",$courseText)){
	  $out["Courses"][] = array(
	    "name"=>$_row["Name"],
	    "description"=>$_row["Description"],
	    "id"=>$_row["Course_ID"],
	    "link"=>"single_course_display.php?id=".$_row["Course_ID"]
	  );
	}
      }
    }

    // Find all theses
    $sql = "SELECT * FROM Thesis";
    $res = mysqli_query($con,$sql);
    if(!$res)
      mysqli_error($con);
    else{
      while($_row = mysqli_fetch_array($res)){
	$thesisText = $_row["Name"]." ".$_row["Abstract"]." ".$_row["Advisor1"]." ".$_row["Advisor2"]." ".$_row["Advisor3"];
	if((preg_match("/".$x."/",$thesisText) && preg_match("/".$y."/",$thesisText) && $x != "" && $y != "") || preg_match("/".$z."/",$thesisText)){
	  $out["Student Projects"][] = array(
	    "name"=>$_row["Name"],
	    "description"=>$_row["Abstract"],
	    "id"=>$_row["Thesis_ID"],
	    "link"=>"single_thesis_display.php?id=".$_row["Thesis_ID"]
	  );
	}
      }
    }
    echo json_encode($out);
    break;
  }
  mysqli_close($con);
}
?>
