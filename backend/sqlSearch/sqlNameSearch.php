<?php 

require("/home/svetlana/www/beta-code/backend/lib.php");

function name_search($name,$databaseTable,$verifyWith,$fieldName){
  $con = sql_connect("svetlana_Total");

  // First clean the name
  // Remove parenthesis
  if ( stripos($name,"(") !== FALSE ){
    $name = substr($name,0,stripos("("));
  }
  // Check to see if the advisor is in the database
  $matches = array();
  $nameCheck = sql_query($con,"SELECT * FROM `$databaseTable` WHERE `$fieldName` = '".sql_escape($con,trim($name))."'");
  if ( mysqli_num_rows($nameCheck) > 0 ){
    while ( $row = mysqli_fetch_array($nameCheck) ){
      if ( $verifyWith["options"] ){
	if ( $verifyWith["options"]["match"] == true ){
	  $match = true;
	  foreach ($verifyWith["fields"] as $index=>$data){
	    $pregParam = "";
	    $loop = explode(" ",$data);
	    $space = "[\_\-\s\.\,\>\<\/\\\[\]\{\}\(\)0-9\~]{0,}";
	    for ( $i = 0, $n = count($loop); $i < $n; $i++ ){
	      $pregParam .= $loop[$i].$space;
	    }
	    if ( preg_match("/$pregParam/",$row[$index]) === 0 )	
	      $match = false;
	  }
	  if ( $match === true )
	    $matches[] = $row[$databaseTable."_ID"];
	}
	else {
	  foreach ($verifyWith["fields"] as $index=>$data){
	    $pregParam = "";
	    $loop = explode(" ",$data);
	    $space = "[\_\-\s\.\,\>\<\/\\\[\]\{\}\(\)0-9\~]{0,}";
	    for ( $i = 0, $n = count($loop); $i < $n; $i++ ){
	      $pregParam .= $loop[$i].$space;
	    }
	    if ( preg_match("/$pregParam/",$row[$index]) === 1 )	
	      if ( in_array($row[$databaseTable."_ID"],$matches) === FALSE )
		$matches[] = $row[$databaseTable."_ID"];
	  }
	}
      }
      else {
	foreach ($verifyWith["fields"] as $index=>$data){
	  if ( stripos($row[$index],$data) === FALSE )
	    $match = false;
	}
      }
    }
  }
  else {
    // If the name doesn't match, do a REGEX to make sure it wasn't missed!
    // Create a REGEXP out of the name
    $names = explode(" ",trim($name));
    $regex = "";
    $lastName = $names[count($names)-1];
    for ( $i = 0, $n = strlen($lastName); $i < $n; $i++ ){
      $regex .= "[".strtolower($lastName[$i]).strtoupper($lastName[$i])."]";
    }
    $nameCheck = sql_query($con,"SELECT * FROM `$databaseTable` WHERE `$fieldName` REGEXP '".mysqli_real_escape_string($con,trim($regex))."'");
    if ( mysqli_num_rows($nameCheck) > 0 ){
      // Confirm the match results with the data provided in 
      while ( $row = mysqli_fetch_array($nameCheck) ){
	// If there is more than one name, then check that the first name matches
	if ( stripos($row[$fieldName],$names[0]) !== FALSE ){
	  // Check that the $verifyWith data matches
	  if ( $verifyWith["options"]["match"] === false ){
	    foreach ($verifyWith["fields"] as $index=>$data){
	      $pregParam = "";
	      $loop = explode(" ",$data);
	      $space = "[\_\-\s\.\,\>\<\/\\\[\]\{\}\(\)0-9\~]{0,}";
	      for ( $i = 0, $n = count($loop); $i < $n; $i++ ){
	      $pregParam .= $loop[$i].$space;
	      }
	      if ( preg_match("/$pregParam/",$row[$index]) === 1 )	
		if ( in_array($row[$databaseTable."_ID"],$matches) === FALSE )
		  $matches[] = $row[$databaseTable."_ID"];
	    }
	  }
	  else {
	    $match = true;
	    foreach ($verifyWith["fields"] as $index=>$data){
	      $pregParam = "";
	      $loop = explode(" ",$data);
	      $space = "[\_\-\s\.\,\>\<\/\\\[\]\{\}\(\)0-9\~]{0,}";
	      for ( $i = 0, $n = count($loop); $i < $n; $i++ ){
		$pregParam .= $loop[$i].$space;
	      }
	      if ( preg_match("/$pregParam/",$row[$index]) === 0 )	
		$match = false;
	    }
	    if ( $match === true ){
	      if ( in_array($row[$databaseTable."_ID"],$matches) === FALSE )
		$matches[] = $row[$databaseTable."_ID"];
	    }
	  }
	}
      }
    }
  }
  return $matches;
}

?>
