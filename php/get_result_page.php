<?php 
session_start();
if ( !isset($_SESSION["data"]) )
  die("No results stored");
else {
  $page  = $_GET["page"]; // Result page #
  $limit = $_GET["limit"]; // The number of results to return
  $type  = $_GET["type"]; // Resource type
  $results = &$_SESSION["data"];
  
  switch ($type){
      case "advisors":{
	$type = "Advisor";
	break;
      }
      case "courses":{
	$type = "Course";
	break;
      }
      case "theses":{
	$type = "Thesis";
	break;
      }
      case "grants":{
	$type = "Grant";
	break;
      }
  }

  $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
    if (mysqli_connect_errno($con))
        echo "Failed to connect to MySQL: " . mysqli_connect_error($con);

  if ( isset($results[$type]) ){
    $results = &$results[$type];
    $desc    = array("Advisor"=>"`Block`","Course"=>"`Description`","Grant"=>"`Description`","Thesis"=>"`Abstract`");
    $start   = ($page-1)*10;
    $end     = $page*10;
    $out     = array();
    for ( $i = $start, $n = $end, $t = count($results); $i < $t && $i < $n; $i++ ){
      $out[] = $results[$i];
      $index = count($out)-1;
      // Get the block for this resource!
      $sql = "SELECT " . $desc[$type] . " FROM `$type` WHERE `".$type."_ID`=".$results[$i]["id"];
      $result = mysqli_query($con,$sql);
      if ( !$result )
	$out[$index]["block"] = mysqli_error($con);
      else {
	while ( $row = mysqli_fetch_array($result) ){
	  $out[$index]["block"] = $row[str_replace("`","",$desc[$type])];
	}
      }
    }
  }
  mysqli_close($con);
  echo json_encode($out);
}
?>
