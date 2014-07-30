<?php 
require("/home/svetlana/www/beta-code/php/get_info.php");
if ( !isset($_SESSION["data"]) )
  die("No results stored");
else {
  $data = json_decode($_GET["data"],true);
  $page  = $data["page"]; // Result page #
  $limit = $data["limit"]; // The number of results to return
  $type  = $data["display"]; // Resource type
  $delims = $data["delimiters"];
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

  $out = array();
  foreach ($results as $resType=>$data){
    if ( isset($results[$resType]) ){
      $tempResults = &$results[$resType];
      $desc    = array("Advisor"=>"`Block`","Course"=>"`Description`","Grant"=>"`Description`","Thesis"=>"`Abstract`");
      $start   = ($page-1)*$limit;
      $end     = $page*$limit;
      $counter = 0;
      $out[$resType]     = array("results_length"=>0,"results"=>array());

      // Count the relevant results
      $relResults = array();
      for ( $i = 0, $n = count($tempResults); $i < $n; $i++ ){
	// Delimit the results!
	if ( isset($delims["departments"]) ){
	  if ( !in_array($tempResults[$i]["department"],$delims["departments"]) ){
	    continue;
	  }
	}
	// If type is advisor, check for funding resources!
	if ( $resType == "Advisor" ){
	  if ( isset($delims["funding"]) ){
	    if ( in_array("hasFunding",$delims["funding"]) ){
	      $res = json_decode(get_info($tempResults[$i]["id"],"Advisor"),true);

	      if ( $res == null || $res == "" || !is_array($res) )
		continue;

	      if ( !isset($res["Funding"]) )
		continue;
	      
	      if ( count($res["Funding"]) == 0 )
		continue;
	    }
	  }
	}	
	$counter++;
	$relResults[] = $tempResults[$i];
      }
      $out[$resType]["results_length"] = $counter;
      if ( $type == $resType ){ 
	$out[$type]["results"] = array_slice($relResults,$start,$limit);
	for ( $i = 0, $n = count($out[$type]["results"]); $i < $n; $i++ ){
	  // Get the block for this resource!
	  $sql = "SELECT " . $desc[$type] . " FROM `$type` WHERE `".$type."_ID`=".$out[$type]["results"][$i]["id"];
	  $result = mysqli_query($con,$sql);
	  if ( !$result )
	    $out[$type]["results"][$i]["block"] = mysqli_error($con);
	  else {
	    while ( $row = mysqli_fetch_array($result) ){
	      $out[$type]["results"][$i]["block"] = $row[str_replace("`","",$desc[$type])];
	    }
	  }
	  if ( count($out[$type]["results"]) >= $limit )
	    break;
	}
      }
    }
  }
  mysqli_close($con);
  echo json_encode($out);
}
?>
