<?php 

require("/home/svetlana/www/beta-code/backend/lib.php");

function name_search($name,$databaseTable,$verifyWith,$fieldName){
  $con = sql_connect("svetlana_Total");
  // Check to see if the advisor is in the database
  $matches = array();
  $nameCheck = sql_query($con,"SELECT * FROM `$databaseTable` WHERE `$fieldName` = '".sql_escape($con,trim($name))."'");
  if ( mysqli_num_rows($nameCheck) > 0 ){
    while ( $row = mysqli_fetch_array($nameCheck) ){
      if ( $verifyWith["options"] ){
	if ( $verifyWith["options"]["match"] ){
	  $match = true;
	  foreach ($verifyWith as $index=>$data){
	    if ( stripos($row[$index],$data) === FALSE )
	      $match = false;
	  }
	  if ( $match === true )
	    $matches[] = $row[$databaseTable."_ID"];
	}
	else {
	  foreach ($verifyWith as $index=>$data){
	    if ( stripos($row[$index],$data) === FALSE )
	      $match = false;
	}
      }
      else {
	foreach ($verifyWith as $index=>$data){
	  if ( stripos($row[$index],$data) === FALSE )
	    $match = false;
      }
    }
  }
  else {
    // If the name doesn't match, do a REGEX to make sure it wasn't missed!
    // Create a REGEXP out of the name
    $names = explode(" ",trim($name));
    $regex = "^\s*";
    for ( $i = 0, $n = strlen($names[0]); $i < $n; $i++ ){
      $regex .= "[".strtolower($names[0][$i]).strtoupper($names[0][$i])."]";
    }
    $nameCheck = sql_query($con,"SELECT * FROM `$databaseTable` WHERE `$fieldName` REGEXP '".mysqli_real_escape_string($con,trim($regex))."'");
    if ( mysqli_num_rows($nameCheck) > 0 ){
      // That advisor's name already exists in the database. Check to see if the university matches up
      while ( $row = mysqli_fetch_array($nameCheck) ){
	// Check the last name
	if ( stripos($row[$fieldName],$names[count($names)-1]) !== FALSE ){
	  // Check that the $verifyWith data matches
	  foreach ($verifyWith as $index=>$data){
	    if ( stripos($row[$index],$data) !== FALSE )	
	      $matches[] = $row[$databaseTable."_ID"];
	  }
	}
      }
    }
  }
  return $matches;
}

?>
