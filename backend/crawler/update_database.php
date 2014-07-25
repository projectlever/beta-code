<?php

// Include the name fixer object
include("NameFixer.php");
header('Content-Type:text/html; charset:UTF-8');

// Constants 
DEFINE("REPLICAS",-2);
DEFINE("INSERT",3);
DEFINE("UPDATE",4);

function sql_connect(){
  $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
  if (mysqli_connect_errno($con)){
    report("incomplete","","","Failed to connect to MySQL: ". mysqli_error($con));
    return FALSE;
  }
  return $con;
}
function backup_information($data,$type,$backup_file){
  if ( file_exists($backup_file) ){
    $json = json_decode(file_get_contents($backup_file),true);
    if ( $json == null )
      $json = array();
  }
  else
    $json = array();
  if ( !isset($json[$type]) )
    $json[$type] = array();
  $json[$type][$data[$type."_ID"]] = $data;
  return file_put_contents($backup_file,json_encode($json));
}
function cleanName($name,$type){
  if ( $type == "Advisor" ){
    // Clean the name by removing identifiers that could break the name matching algorithm
    $remove = array(
      array(
	"/[dD]{1}[rR]{1}\s{0,1}\.*/",
	"/[mM]{1}[rR]{1}\s{0,1}\.*/",
	"/[mM]{1}[dD]{1}\s{0,1}\.*/",
	"/[pP]{1}[hH]{1}\s{0,1}[dD]{1}\s*\.*/"
      ),
      array(
	"chair","chairman","doctor","provost","director","professor","assistant"
      )
    );
    foreach ($remove[0] as $index=>$rm){
      $name = preg_replace($rm,"",$name);
    }
    foreach ($remove[1] as $index=>$rm){
      $name = str_ireplace($rm,"",$name);
    }
    return $name;
  }
  return $name;
}
// This function checks to see if the thing (advisor, course, thesis, etc...) has already been imported. If it has, we are going to UPDATE
// rather than INSERT the thing's information into the database
function previousImport($name,$info,$type,$sql){
  $name = cleanName($name,$type);
  // Check to see if the advisor is in the database
  $matches = array();
  $nameCheck = $sql->query("SELECT * FROM `$type` WHERE `Name` = '".$sql->escape(trim($name))."'");
  if ( $nameCheck === FALSE )
    return FALSE;
  if ( mysqli_num_rows($nameCheck) > 0 ){
    while ( $row = mysqli_fetch_array($nameCheck) ){
      if ( stripos($row["University"],str_replace(" ","_",$info["university"])) !== FALSE && stripos($row["School"],$info["school"]) !== FALSE && stripos($row["Department"],$info["department"]) !== FALSE ){
	$matches[] = $row;
	backup_information($row,"Advisor",$backup);
      }
    }
  }
  else {
    if ( $type != "Advisor" ){
      return FALSE;
    }
    // If the name doesn't match, do a REGEX to make sure it wasn't missed!
    // Create a REGEXP out of the name
    $names = explode(" ",trim($name));
    $regex = "^\s*";
    for ( $i = 0, $n = strlen($names[0]); $i < $n; $i++ ){
      $regex .= "[".strtolower($names[0][$i]).strtoupper($names[0][$i])."]";
    }
    $nameCheck = $sql->query("SELECT * FROM `$type` WHERE `Name` REGEXP '".$sql->escape(trim($regex))."'");
    if ( $nameCheck === FALSE ){
      return FALSE;
    }
    if ( mysqli_num_rows($nameCheck) > 0 ){
      // That advisor's name already exists in the database. Check to see if the university matches up
      $matches = array();
      while ( $row = mysqli_fetch_array($nameCheck) ){
	// Check the last name
	if ( stripos($row["Name"],$names[count($names)-1]) !== FALSE ){
	  // Check that the university matches
	  if ( stripos($row["University"],str_replace(" ","_",$info["university"])) !== FALSE && stripos($row["School"],$info["school"]) !== FALSE && stripos($row["Department"],$info["department"]) !== FALSE ){
	    $matches[] = $row;
	  }
	}
      }
    }
  }
  if ( count($matches) > 0 ){
    return $matches;
  }
  else
    return FALSE;
}
function update_database_course($data, $log, $sql, $backup){
  $data["university"] = preg_replace("/\s/","_",$data["university"]);

  // Verify that the course isn't already in the database
  $result = $sql->query("SELECT * FROM `Course` WHERE `Name`='".trim($sql->escape($data["name"]))."'");
  if ( $result === FALSE ){  
    plog($log,"MySQL error on row $rowNumber in sheet $sheetName: " . $sql->get_error());
    return FALSE;
  }
  else {
    if ( mysqli_num_rows($result) > 0 ){
      // The course already exists! Let's check the description. We will keep the bigger one.
      while ( $row = mysqli_fetch_array($result) ){
	$blob = $row["Blob"];
	backup_information($row,"Course",$backup);
	preg_match("/\S/",$row["Blob"],$chars);
	if ( strlen($data["block"]) > strlen($row["Description"]) ){
	  // Add the old blob in!
	  if ( stripos($blob,$row["Description"]) === FALSE ){
	    // Update the blob!
	    $update = $sql->query("UPDATE `Course` SET `Blob`='".$sql->escape($blob." ".$row["Description"])."' WHERE `Course_ID`=".$row["Course_ID"]);
	    if ( $update !== FALSE )
	      plog($log,"Blob updated for Course " . $row["Course_ID"]);
	    else {
	      plog($log,"Error when updating blob for course: " . $data["name"] . ". Error: " . $sql->get_error());
	    }	      
	  }
	  // Replace the description! Then exit the function
	  $update = $sql->query("UPDATE `Course` SET `Description`='".$sql->escape($data["block"])."' WHERE `Course_ID`=".$row["Course_ID"]);
	  if ( $update !== FALSE ){
	    plog($log,"updated description for course " . $row["Course_ID"]);
	    return TRUE;
	  }
	  else {
	    plog($log,"Error when updating blob for course: " . $data["name"] . ". Error: " . $sql->get_error());
	    return FALSE;
	  }	      
	}
	else if ( strlen($data["block"]) == strlen($row["Description"]) || count($chars) == 0 ){
	  // Update the blob!
	  $update = $sql->query("UPDATE `Course` SET `Blob`='".$sql->escape($data["block"]." ".$data["name"]." ".$data["faculty"])."' WHERE `Course_ID`=".$row["Course_ID"]);
	  if ( $update !== FALSE )
	    plog($log,"Updated Blob for Course " . $row["Course_ID"]);
	  else {
	    plog($log,"Error when updating blob for course: " . $data["name"] . ". Error: " . $sql->get_error());
	    return FALSE;
	  }	      
	}
	else {
	  if ( stripos($blob,$data["block"]) === FALSE ){
	    // Add the new info to the blob
	    $update = $sql->query("UPDATE `Course` SET `Blob`='".$sql->escape($blob." ".$data["block"])."' WHERE `Course_ID`=".$row["Course_ID"]);
	    if ( $update !== FALSE )
	      plog($log,"Updated blob for Course " . $row["Course_ID"]);
	    else {
	      plog($log,"Error when updating blob for course: " . $data["name"] . ". Error: " . $sql->get_error());
	      return FALSE;
	    }	      
	  }
	}
      }
    }
    else {
      // New entry! So insert it
      $insert = $sql->query("INSERT INTO `Course` (`Name`,`Description`,`Faculty`,`School`,`Department`,`University`) VALUES ('".$sql->escape($data["name"])."','".$sql->escape($data["block"])."','".$sql->escape($data["faculty"])."','".$sql->escape(trim($data["school"]))."','".$sql->escape(trim($data["department"]))."','".$sql->escape(trim($data["university"]))."')");
      if ( $insert !== FALSE ){
	plog($log,"Inserted new course: " . $data["name"]."\r\n");
	return TRUE;
      }
      else {
	plog($log,"Error when inserting course: " . $data["name"] . ". Error: " . $sql->get_error());
	return FALSE;
      }	      
    }
  }
}
function update_database_thesis($data, $log, $sql, $backup){
  $data["university"] = preg_replace("/\s/","_",$data["university"]);
  // Verify that this thesis hasn't been inserted into the database yet
  $result = $sql->query("SELECT * FROM `Thesis` WHERE `Name`='".$sql->escape($data["name"])."' AND `University`='".$sql->escape($data["university"])."'");
  if ( $result === FALSE ){
    plog($log,"Error when selecting thesis: " . $data["name"]." Error: " . $sql->get_error());
    return FALSE;
  }
  else {
    if ( mysqli_num_rows($result) > 0 ){
      $blob = "";
      while ( $row = mysqli_fetch_array($result) ){
	$blob = $row["Blob"];	  
	backup_information($row,"Thesis",$backup);
	$old_abstract = $row["Abstract"];
	// See if the new abstract is bigger than the old abstract
	if ( strlen($data["block"]) > strlen($row["Abstract"]) ){
	  // Replace it!
	  $update = $sql->query("UPDATE `Thesis` SET `Abstract`='".$sql->escape($data["block"])."' WHERE `Thesis_ID`=".$row["Thesis_ID"]);
	  if ( $update !== FALSE )
	    plog($log,"Successfully updated thesis abstract: " . $row["Thesis_ID"]);
	  else {
	    plog($log,"Error when updating thesis abstract: " . $row["Thesis_ID"] . " Error: " . $sql->get_error());
	    return FALSE;
	  }
	}
	else {
	  // Update the Blob
	  if ( stripos($blob,$data["block"]) === FALSE ){
	    $update = $sql->query("UPDATE `Thesis` SET `Blob`='".$sql->escape($blob." ".$data["block"])."' WHERE `Thesis_ID`=".$row["Thesis_ID"]);
	    if ( $update !== FALSE )
	      plog($log,"Updated blob with new data for thesis: " . $row["Thesis_ID"]);
	    else {
	      plog($log,"Error when updating thesis blob: " . $row["Thesis_ID"] . " Error: " . $sql->get_error());
	      return FALSE;
	    }
	  }
	}
	// Update the Blob
	if ( stripos($blob,$old_abstract) === FALSE ){
	  $update = $sql->query("UPDATE `Thesis` SET `Blob`='".$sql->escape($blob." ".$old_abstract)."' WHERE `Thesis_ID`=".$row["Thesis_ID"]);
	  if ( $update !== FALSE )
	    plog($log,"Updated blob for thesis: " . $row["Thesis_ID"]);
	  else {
	    plog($log,"Error when updating thesis blob: " . $row["Thesis_ID"] . " Error: " . $sql->get_error());
	    return FALSE;
	  }
	}
	else {
	  // Insert it!
	  $insert = $sql->query("INSERT INTO `Thesis` (`Name`,`Abstract`,`Year`,`Department`,`School`,`University`,`Blob`,`Author`,`Advisor1`) VALUES ('".$sql->escape($data["name"])."','".$sql->escape($data["block"])."','".$sql->escape($data["year"])."','".$sql->escape($data["department"])."','".$sql->escape($data["school"])."','".$sql->escape($data["university"])."','".$sql->escape($data["name"]." ".$sql->escape($data["block"]))."','".$sql->escape($data["author"])."','".$sql->escape($data["faculty"])."')");
	  if ( $insert !== FALSE )
	    plog($log,"Inserted new thesis: " . $data["name"]);
	  else
	    plog($log,"Error occurred when inserting new thesis: " . $data["name"]);
	}
      }
    }
  }
}
function update_database_funding($data, $log, $sql, $backup){
  $data["university"] = preg_replace("/\s/","_",$data["university"]);
  // Check for previous import
  $result = $sql->query("SELECT * FROM `Funding` WHERE `Name`='".$sql->escape(trim($data["name"]))."' AND `University`='".$sql->escape($data["university"])."'");
  if ( $result === FALSE ){
    plog($log,"Error occurred when selecting funding: " . $data["name"]);
    return FALSE;
  }
  else if ( gettype($result) == "string" ){
    plog($log,"Mysqli result is a string?? => " . $result);
    return FALSE;
  }
  else {
    if ( mysqli_num_rows($result) > 0 ){
      while ( $row = mysqli_fetch_array($result) ){
	$blob = $row["Blob"];
	backup_information($row,"Funding",$backup);
	// Keep the larger abstract ($info)
	if ( strlen($data["block"]) > strlen($row["Abstract"]) ){
	  if ( stripos($blob,$row["Abstract"]) === FALSE ){
	    $sql->query("UPDATE `Funding` SET `Abstract`='".$sql->escape($blob." ".$row["Abstract"])."', `Link`='".$sql->escape($data["link"])."' WHERE `Funding_ID`=".$row["Funding_ID"]);
	    plog($log,"Error occurred when updating funding: " . $row["Funding_ID"] . " Error: " . $sql->get_error());
	  }
	  $update = $sql->query("UPDATE `Funding` SET `Abstract`='".$sql->escape($data["block"]).", `Link`='".$sql->escape($data["link"])."' WHERE `Funding_ID`=".$row["Funding_ID"]);
	  if ( $update === FALSE )
	    plog($log,"Failed to update funding abstract: " . $row["Funding_ID"]. " Error: " . $sql->get_error());
	}
	else if ( strlen($data["block"]) == strlen($row["Abstract"]) || strlen($data["block"]) < strlen($row["Abstract"]) ){
	  if ( stripos($blob,$data["block"]) === FALSE ){
	    if ( $sql->query("UPDATE `Funding` SET `Abstract`='".$sql->escape($blob." ".$data["block"])."', `Link`='".$sql->escape($data["link"])."' WHERE `Funding_ID`=".$row["Funding_ID"]) === FALSE )
	      plog($log,"Failed to update funding abstract: " . $row["Funding_ID"] . " Error: " . $sql->get_error());
	  }
	}
      }
    }
    else {
      // Insert it!
      $insert = $sql->query("INSERT INTO `Funding` (`Name`,`Abstract`,`Department`,`School`,`University`,`FirstNamePI`,`Link`) VALUES ('".$sql->escape($data["name"])."','".$sql->escape($data["block"])."','".$sql->escape($data["department"])."','".$sql->escape($data["school"])."','".$sql->escape($data["university"])."','".$sql->escape($data["manager"])."','".$sql->escape($data["link"])."')");
    }
  }
}
function getIOData($data,$sheet_data,$n){
  $iodata = &$data["ioObject"];
  if ( $n >= count($iodata) )
    return FALSE;
  $iodata = $iodata[$n];
  if ( gettype($iodata) != 'object' ){
    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Result from Import.IO is not a valid object! Added as a manual import","webpage"=>$data["webpage"],"result"=>$data));
      add_manual_import(array("webpage"=>$data["webpage"],"data"=>$data));
    return FALSE;
  }
  return $iodata;
}
function update_database_advisor($data, $log, $sql, $backup){
  // All data returned from Import.IO may OR may not be an array. ALWAYS check for an array
  $test = previousImport($data["name"],$data,"Advisor",$sql);

  // Create a fixer object to normalize the name
  $fixer = new NameFixer();
  $data["name"] = $fixer->properize($data["name"]);

  // Prepare data
  
  // Replace header, picture, name, and info in block
  $block = str_replace(array($data["header"],$data["picture"],$data["name"],$data["info"]),"",$data["block"]);
  
    // Remove picture and name from the header (if it exists)
  $header = str_replace(array($data["picture"],$data["name"]),"",$data["header"]);
  
  $import["name"]    = $data["name"];
  $import["block"]   = $block;
  $import["picture"] = $data["picture"];
  $import["header"]  = $header;
  $import["info"]    = $data["info"];
  $import["department"] = $data["department"];
  $import["school"] = $data["school"];
  $import["university"] = str_replace(" ","_",$data["university"]);
  
  if ( $test !== FALSE ){
    // Update the information
    // Keep the block that has the most information. This also means that we should include the header that goes with that block
    if ( strlen($import["block"]) > strlen($test["Block"]) ){
      // Keep the new one
      if ( updateAdvisor($import,$sql->get_con(),$test) === FALSE ){ // Send test which contains the advisor's ID
	plog($log,"Error occurred when update advisor information: " . $sql->get_error());
      }
    }
    else {
      // Keep the old one but insert the data into the blob for use by the algorithm
      if ( insertDataIntoBlob($import,$sql->get_con(),$test) === FALSE )
	plog($log,"Error occurred when updating advisor blob data: " . $sql->get_error());
    }
  }
  else {
    if ( $test == FALSE )
      if ( insertAdvisor($import,$sql->get_con()) === FALSE )
	plog($log,"Error occurred when inserting advisor: " . $sql->get_error());
  }
}
function insertDataIntoBlob($import,$con,$old_data){
  $block      = mysqli_real_escape_string($con,$import["block"]);
  $header     = mysqli_real_escape_string($con,$import["header"]);
  // Get the Blob
  $result = runSQL($con,"SELECT * FROM `Advisor` WHERE `Advisor_ID`=".$old_data["Advisor_ID"]);
  if ( $result == FALSE ){
    return;
  }
  else {
    // Check to see if the block and header are already in the blob. If they're not, then add them in!
    while ($row = mysqli_fetch_array($result)){
      if ( $row["Block"] != $import["block"] ){
	$blob = $row["Blob"];
	if ( stripos($blob,$block) === FALSE )
	  $blob .= " $block";
	if ( stripos($blob,$header) === FALSE )
	  $blob .= " $header";

	return runSQL($con,"UPDATE `Advisor` SET `Blob`='".mysqli_real_escape_string($con,$blob)."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"]);
      }
      // Make sure that the link isn't empty
      preg_match("/\S/",$row["Link"],$chars);
      if ( count($chars) === 0 ){
	return runSQL($con,"UPDATE `Advisor` SET `Link`='".mysqli_real_escape_string($con,$import["link"])."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"]);
      }
    }
    // Otherwise, check to see if this advisor already has the new department, school, and link in their SQL field
    $result = runSQL($con,"SELECT `Department`,`School`,`Link` FROM `Advisor` WHERE `Advisor_ID`=".$old_data["Advisor_ID"]);
    if ( $result === FALSE ){
      return FALSE;
    }
    else {
      while ( $row = mysqli_fetch_array($result) ){
	$department = cleanJSONArray($row["Department"],str_replace("_"," ",$import["department"]));
	$school = cleanJSONArray($row["School"],str_replace("_"," ",$import["school"]));
      }
      $result = runSQL($con,"UPDATE `Advisor` SET `Department`='".mysqli_real_escape_string($con,$department)."', `School`='".mysqli_real_escape_string($con,$school)."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"]);
      return $result;
    }
  }
}
function updateAdvisor($data,$con,$old_data){
  $block      = mysqli_real_escape_string($con,$data["block"]);
  
  // If the new header is empty, keep the old header
  preg_match("/\S/",$data["header"],$chars);
  if ( count($chars) == 0 )
    $header = mysqli_real_escape_string($con,$old_data["Header"]);
  else 
    $header = mysqli_real_escape_string($con,$data["header"]);    
  
  // If the new info is empty, keep the old info!
  preg_match("/\S/",$data["info"],$chars);
  if ( count($chars) == 0 )
    $info = mysqli_real_escape_string($con,$old_data["Info"]);
  else 
    $info = mysqli_real_escape_string($con,$data["info"]);    

  $department = str_replace("_"," ",$data["department"]);
  $school     = str_replace("_"," ",$data["school"]);
  $select = "SELECT * FROM `Advisor` WHERE `Advisor_ID`=".$old_data["Advisor_ID"];
  $result = mysqli_query($con,$select);
  if ( $result === FALSE ){
    return FALSE;
  }
  else {
    $blob = "";
    $department = array();
    $school = array();
    while ($row = mysqli_fetch_array($result)){
      $department = cleanJSONArray($row["Department"],$department);
      $school     = cleanJSONArray($row["School"],$school);
      $blob       = $row["Blob"];
    }
    $blob = mysqli_real_escape_string($con,$data["block"]." ".$data["header"]." ".$blob);
    $department = mysqli_real_escape_string($con,$department);
    $school     = mysqli_real_escape_string($con,$school);
    $update = "UPDATE `Advisor` SET `School`='$school', `Department`='$department', `Header`='$header', `Blob`='$blob', `Link`=>'".mysqli_real_escape_string($con,$data["link"])."',";    
    $update .= "`Block`='$block', `Info`='$info',`Link`='".mysqli_real_escape_string($con,$import["link"])."',`University`='".$old_data["University"]."', `Scraped_Level`='Secondary', `Picture`='".$data["picture"]."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"];
    // The next line is for debugging purposes and is probably commmented out
    //echo $update;exit;
    return mysqli_query($con,$update);
  }
}
function logSuccess($old_data,$data,$message){
  global $results_file, $backup_file;
  if ( file_exists($results_file) ){
    $json = json_decode(file_get_contents($results_file));
    if ( $json == null )
      $json = array();
  }
  else 
    $json = array();
  if ( $message )
    $json[] = array("message"=>$message,"old_data"=>$old_data,"new_data"=>$data,"backup_file"=>$backup_file);
  else
    $json[] = array("message"=>"Successful update","old_data"=>$old_data,"new_data"=>$data,"backup_file"=>$backup_file);
  file_put_contents($results_file,json_encode($json));
}
function insertAdvisor($data,$con){
  $department = mysqli_real_escape_string($con,json_encode($data["department"],JSON_FORCE_OBJECT));
  $university = mysqli_real_escape_string($con,$data["university"]);
  $school     = mysqli_real_escape_string($con,json_encode($data["school"],JSON_FORCE_OBJECT));
  $block      = mysqli_real_escape_string($con,$data["block"]);
  $header     = mysqli_real_escape_string($con,$data["header"]);

  $insert  = "INSERT INTO `Advisor` (`Name`,`University`,`School`,`Department`,`Picture`,`Block`,";
  $insert .= "`Header`,`Scraped_Level`) VALUES (";
  $insert .= "'".mysqli_real_escape_string($con,$data["name"])."','$university','$school','$department','".mysqli_real_escape_string($con,$data["picture"])."','$block',";
  $insert .= "'$header','Secondary')";
  
  return runSQL($con,$insert);
}
function runSQL($con,$query_string){
  global $log_file;
  $result = mysqli_query($con,$query_string);
  if ( !$result ){
    plog($log_file,mysqli_error($con));
    return FALSE;
  }
  else {
    return $result;
  }
}
/*
 * The cleanJSONArray function takes a json string representing an array of data for a professor. For example: $json may be  a json string
 * containing the departments that a professor is associated with. $string is the new information that we are looking to import into the database.
 * $string may be a department name, a school name, a link, or any other information we may be importing. The function decodes the json string into
 * an array and then compares all of the information in the array to the new information ($string). If it cannot find $string inside the array, then
 * it pushes $string into the array and re-encodes the json string.
 */
function cleanJSONArray($json,$string){
  $array = json_decode($json,true);
  
  if ( gettype($string) == "array" )
    $string = implode(",",$string);
  // HOPEFULLY this code doesn't have to fire
  if ( $array === false || $array == null || is_array($array) === false ){
    // $array is a string...duh
    if($array != $string){
      $newarray = array($array,$string);
      $array = $newarray;
    }
  }
  else if ( is_array($array) === true ){
    // If $department is a JSON object, loop through the entire array looking for a match between those strings
    // and the provided string
    $isEqual = false;
    for ( $i = 0, $n = count($array); $i < $n; $i++ ){
      if ( trim($array[$i]) == trim($string) ){
        // If the link matches an element in the array, set the isEqual variable to true, which will tell
        // the program not to add the link to the SQL.
        $isEqual = true;
        $i = $n;
      }
    }
    if ( $isEqual === false ){
      // If the link from $advisors was NOT matched with a link in the SQL, then add it to the SQL!
      array_push($array,trim($string));
    }
  }
  else {
    report("incomplete","","",array("message"=>"Bad JSON detected","json"=>$array));
  }
  // Remove all departments and schools with underscores (_)
  foreach ($array as $index=>$name){
    if ( stripos($name,"_") !== FALSE )
      unset($array[$index]);
  }
  if ( count($array) == 0 ){
        report("incomplete","","",array("message"=>"Bad JSON detected","json"=>$array));
  }
  $array = json_encode($array,JSON_FORCE_OBJECT);
  
  return $array;
}
?>
