<?php

$notify = "stone.ejoseph@gmail.com";

ini_set("auto_detect_line_endings", "1");
require("/home/svetlana/www/beta-code/backend/sql.php");
require("/home/svetlana/www/beta-code/backend/crawler/update_database.php");

$sql = new SQL();
$fix = new NameFixer();

// Analytics
$success = array(
  "Advisor"=>0,
  "Course"=>0,
  "Grant"=>0,
  "Funding"=>0,
  "Thesis"=>0,
);
$failed = array(
  "Advisor"=>0,
  "Course"=>0,
  "Grant"=>0,
  "Funding"=>0,
  "Thesis"=>0,
);
$total = array(
  "Advisor"=>0,
  "Course"=>0,
  "Grant"=>0,
  "Funding"=>0,
  "Thesis"=>0,
);

$categories = array(
  "Advisor" => array("University","Block","Picture","Department","School","Name","Header","Email","Info","Blob"),
  "Course" => array("University","School","Department","Name","Description","Faculty","Blob"),
  "Thesis" => array("University","School","Department","Author","Name","Abstract","Advisor1","Blob"),
  "Funding" => array("Name","Link","Manager","University","School","Department","Abstract","Blob","Co-PINames"),
  "Grant"=> array("University","School","Description","Email","Sponsor","Name","Blob")
);
$removeHTML = array( // Categories to use strip_tags() on 
  "Advisor" => array("Name","Header","Blob"),
  "Course" => array("Name","Blob"),
  "Thesis" => array("Name","Advisor1","Blob","Author"),
  "Funding" => array("Name","Blob"),
  "Grant"=> array("Name","Blob")
);
$blobFields = array(
  "Advisor" => array("Block","Header","Info"),
  "Course" => array("Name","Block","Faculty"),
  "Thesis" => array("Name","Advisor1","Abstract"),
  "Funding" => array("Name","Info","Abstract"),
  "Grant"=> array("Name","Sponsor","Description")
);

$log_file      = "./logs/csv_" . date("m_d_y_H_i_s") . "_log.json";
$results       = "./results/csv_". date("m_d_y_H_i_s") . "_log.json";
$backup_file   = "./backups/csv_" . date("m_d_y_H_i_s") . ".json";
	
$rootpath = './csv/';
$fileinfos = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($rootpath)
);
foreach($fileinfos as $pathname => $fileinfo) {
  if (!$fileinfo->isFile()) continue;
  // Read the file info an array
  plog($log_file,"Reading CSV: $pathname");
  $f = fopen($pathname,"r+");
  if ( $f ){
    $titles = fgetcsv($f);
    plog($log_file,"Retrieved headers: ".print_r($titles,true));
    // Find the column with the type of resource
    $typeCol = -1;
    foreach ($titles as $index=>$title){
      if ($title == "Type")
	$typeCol = $index;
    }
    $sql->connect("svetlana_Total");
    $row = 1;
    while ( $data = fgetcsv($f) ){
      $row++;
      if ( $typeCol == -1 )
	continue;
      else
	$type = fixType(ucfirst(strtolower($data[$typeCol])));
      if ( $type != "Advisor" )
	continue;

      $import = array();
      if ( $type == "" ){
	plog($log_file,"'Type' column is empty on row $row in sheet $pathname.");
	continue;
      }
      $total[$type]++;

      foreach ($data as $index=>$value){
	if ( $index == $typeCol ) // Skip the Type column
	  continue;

	// Get the column name for the cell
	$temp_type = $titles[$index];

	// Is the column a type in the categories specified above?
	if ( !isset($categories[$type]) ){
	  plog($log_file,"Category '".$temp_type."' does not exist for resource of type '".$type."'");	  
	}
	else {
	  if ( in_array($temp_type,$categories[$type]) === FALSE ){
	    plog($log_file,"Category '".$temp_type."' does not exist for resource of type '".$type."'");
	  }
	  else {
	    // If it is...then store the information!
	    if ( !$import[$temp_type] )
	      $import[$temp_type] = "";
	    $import[$temp_type] = $value;
	  }
	}
      }
      // Name is an absolutely necessary field. Let's check for it
      if ( !$import["Name"] ){
	plog($log_file,"No 'Name' category provided on line $row in file $pathname");
	continue;
      }
      plog($log_file,"Retrieved information for " . $import["Name"] . " on line $row");

      // Strip tags from all of the fields requiring a strip
      foreach ($removeHTML[$type] as $index=>$cat){
	// Remove all leading and trailing spaces, new-lines, and tabs
	// Use strip tags to remove HTML
	// And trim away leading and trailing white space
	$import[$cat] = preg_replace("/^[\s\r\n\t]{0,}/","",preg_replace("/[\s\r\t\n]{0,}$/","",trim(strip_tags($import[$cat]))));
      }
      
      // Fix the name of the advisor
      if ( $type == "Advisor" )
	$import["Name"] = $fix->properize($import["Name"]);

      foreach ($import as $cat=>$value){
	// Replace all line breaks with <br/> tags and make the strings SQL safe
	$import[$cat] = $sql->escape(preg_replace("/[\r\n]/","<br>",trim($value)));
      }
      
      // For advisors, prepare the schools and departments as JSON objects
      if ( $type == "Advisor" ){
	// Make the department and school a JSON object
	$import["Department"] = $sql->escape(json_encode(array(trim($import["Department"]))));
	$import["School"] = $sql->escape(json_encode(array(trim($import["School"]))));
      }

      // Replace spaces with underscores in the university name
      $university = $sql->escape(str_replace(" ","_",trim($university)));

      // Create the blob
      $import["Blob"] = "";
      foreach ($blobFields[$type] as $index=>$field){
	if ( $import[$field] ){
	  $import["Blob"] .= $import[$field]." ";
	}
	else {
	  plog($log_file,"Attempted to use a field '$field' as part of the blob when it doesn't exist in the categories for '$type'. Row $row in sheet $pathname");
	}
      }

      // Check for a previous import
      if ( $type == "Advisor" ){
	$previous = checkForExistence($import["Name"],$import,"Advisor",$sql);
	if ( $previous == -1 ){
	  plog($log_file,"Error when attempting to match advisor name '".$import["Name"]."'. Error: " . $sql->get_error());
	  $failed["Advisor"]++;
	  continue;
	}
      }
      else {
	$result = $sql->query("SELECT * FROM `$type` WHERE `Name`='".trim($sql->escape($import["Name"]))."'");
	if ( !$result ){
	  plog($log_file,"Error occurred while attempting to find previous information on line $row in sheet $pathname: " . $sql->get_error());
	  continue;
	}
	else {
	  if ( mysqli_num_rows > 0 ){
	    // Previous data!
	    $previous = mysqli_fetch_array($result);
	    // Backup the old information
	    backup_information($previous,$type,$backup_file);
	  }
	  else {
	    // New data!
	    $previous = FALSE;
	  }
	}
      }

      // If $previous is FALSE, then we have a new resource that we just insert. If $previous != FALSE then we need to update the information
      if ( $previous === FALSE ){
	
	// Create the sql command
	$query = "INSERT INTO `$type` (";
	$columns = "";
	$values  = "";
	foreach ($import as $col=>$value){
	  $columns .= "`$col`,";
	  $values  .= "'$value',";
	}

	// Replace the last comma (",") with a close paren (")")
	$query .= preg_replace("/,$/",")",$columns) . " VALUES (";
	$query .= preg_replace("/,$/",")",$values);

	$query = $sql->query($query);
	if ( $query === FALSE ){
	  plog($log_file,"Failed to insert '$type' on row $row in sheet $pathname. Error: " . $sql->get_error());
	  $failed[$type]++;
	}
	else {
	  plog($log_file,"Successfully inserted '$type'");
	  $success[$type]++;
	}
      }
      else {
	/* Now we have to UPDATE the information...Steps:
	 * 1. Take old information that went into blob (AKA: $blobFields) and insert that data into the `Blob` column in the database
	 * 2. Insert the new information
	 */
	
	// Lowercase all of the new data
	foreach ($data as $name=>$value){
	  $data[strtolower($name)] = $value;
	  unset($data[$name]);
	}
	if ( updateAdvisor($data,$sql->get_con(),$previous) === FALSE )
	  $failed[$type]++;
	else
	  $success[$type]++;
      }
    }
    $sql->close();
  }
  fclose($f);
}

$out = "<table><tbody><tr><td><h3>CVS Parser Results</h3></td></tr>";
$out = "<table><tbody><tr><td colspan='2'><h4>Total resources:</h4></td></tr>";
foreach ($total as $type=>$n){
  if ( $n > 0 && $type != "" )
    $out .= "<tr><td>".$type.": </td><td>" . $n."</td></tr>";
}
$out .= "<tr><td colspan='2'><h5>Successful:</h5></td></tr>";
foreach ($success as $type=>$n){
  if ( $n > 0 && $type != "" )
    $out .= "<tr><td>".$type . ":</td><td>" . $n . "</td></tr><tr><td>Percent Success:</td><td>" . ($n/$total[$type]*100)."%</td></tr>";
}
$out .= "<tr><td><h5>Failed:</h5></td></tr>";
foreach ($failed as $type=>$n){
  if ( $n > 0 && $type != "" )
    $out .= "<tr><td>".$type . ":</td><td>" . $n . "</td></tr><tr><td>Percent Failed:</td><td>" . ($n/$total[$type]*100)."%</td></tr><br/>";
}
$out .= "</tbody></table><br/>";
$out .= "Number of manual imports => " . count($manual) . "<br/>";

$to = $notify;

$subject = 'AutoUpdate Results';

$headers = "";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

mail($to, $subject, $out, $headers);

function fixType($type){
  if ( $type == "Advisors" || $type == "advisors" )
    return "Advisor";
  else if ( $type == "Courses" || $type == "courses" ){
    return "Course";
  }
  else if ( $type == "Theses" || $type == "theses" ){
    return "Thesis";
  }
  else if ( $type == "Grants" || $type == "grants" ){
    return "Grant";
  }
  else
    return $type; // It was actually correct??
}
function plog($file,$msg){
  echo $msg."<br/>";
  file_put_contents($file,$msg."\r\n",FILE_APPEND);
}
function checkForExistence($name,$data,$type,$sql){
  global $log_file,$failed;
  if ( $name == "" )
    return FALSE;
  // Check for exact name match
  $res = $sql->query("SELECT * FROM `$type` WHERE `Name` = '".$sql->escape($name)."' AND `University`='".$data["University"]."'");
  if ( !$res ){
    return -1;
  }
  if ( mysqli_num_rows($res) > 0 ){
    // We have a match!
    return mysqli_fetch_array($res);
  }
  else {
    return FALSE;
    // No match...should probably check with LIKE search
    $search = preg_replace("/[\.\,]/","",$name);
    if ( stripos($search,"(") !== FALSE ){
      if ( stripos($search,")") !== FALSE ){
	$search = preg_replace("/\([\sa-zA-Z\-\'\_\,\.\"\'\*]{0,}\)/","",$search);
      }
      else {
	preg_match("/\(\s*[a-zA-Z\-\'\"\\.\,\_\*]{0,}\s/",$search,$match);
	if ( count($match) > 0 ){
	  $search = preg_replace("/\(\s*[a-zA-Z\-\'\"\\.\,\_\*]{0,}\s/","",$search);
	}
	else {
	  $search = substr($search,0,stripos($search,"("));
	}
      }
    }
    // Replace spaces with % and add % to beginning and end
    $search = preg_replace("/[A-Za-z]{0,}\./","",$search);
    if ( stripos($search,",") !== FALSE ){
      $search = susbtr($search,0,stripos($search,","));
    }
    // Select only first and last name
    $search = explode(" ",preg_replace("/\s{2,}/"," ",$search));
    $search = $sql->escape("%".$search[0]."%".$search[count($search)-1]."%");
    echo $search."<br/>";return false;
    $res = $sql->query("SELECT `".$type."_ID` FROM `$type` WHERE `Name` LIKE '$search' `University`='".$data["University"]."'");
    if ( !$res ) return -1;
    else {
      if ( mysqli_num_rows($res) > 0 ){
	return mysqli_fetch_array($res);
      }
      else {
	return FALSE;
      }
    }
  }
}
?>
