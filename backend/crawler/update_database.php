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
	"/DR\./","/dr\./","/dR\./","/Dr\./"
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
  global $backup_file;
  $backup = $backup_file;

  $name = cleanName($name,$type);
  // Check to see if the advisor is in the database
  $matches = array();
  $nameCheck = $sql->query("SELECT * FROM `$type` WHERE `Name` = '".$sql->escape(trim($name))."'");
  if ( $nameCheck === FALSE )
    return -1;
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

      // The course already exists! Update the information by placing the old info into the blob and replacing the description field with the new data!
      while ( $row = mysqli_fetch_array($result) ){
	$blob = $row["Blob"];

	// Backup the information in case the data spontaneously combusts
	backup_information($row,"Course",$backup); 

	// Add the old description to the blob if it's not already in there!
	if ( stripos($blob,$row["Description"]) === FALSE ){

	  // Update the blob!
	  $update = $sql->query("UPDATE `Course` SET `Blob`='".$sql->escape($blob." ".$row["Description"])."' WHERE `Course_ID`=".$row["Course_ID"]);
	  if ( $update !== FALSE )
	    plog($log,"Blob updated for Course " . $row["Course_ID"]);
	  else {
	    plog($log,"Error when updating blob for course: " . $data["name"] . ". Error: " . $sql->get_error());
	  }
	}	      

	// Add in the new description
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
    }
    else {
      // No old data found under that name...so it's new! Let's insert it
      $insert = $sql->query("INSERT INTO `Course` (`Name`,`Description`,`Faculty`,`School`,`Department`,`University`) VALUES ('"
			   .$sql->escape($data["name"])."','"
			   .$sql->escape($data["block"])."','"
			   .$sql->escape($data["faculty"])."','"
			   .$sql->escape(trim($data["school"]))."','"
		           .$sql->escape(trim($data["department"]))."','"
			   .$sql->escape(trim($data["university"]))."')");
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
	
	// Let's backup the data in case it gets sucked into a black hole, never to be seen again
	backup_information($row,"Thesis",$backup);

	$old_abstract = $row["Abstract"];
	// Keep the updated information! And update the blob with the old abstract if it's not already there!
	if ( stripos($blob,$old_abstract) === FALSE ){
	  $update = $sql->query("UPDATE `Thesis` SET `Abstract`='".$sql->escape($blob." ".$old_abstract)."' WHERE `Thesis_ID`=".$row["Thesis_ID"]);
	}

	// Now, insert the new data into the abstract field
	$update = $sql->query("UPDATE `Thesis` SET `Abstract`='".$sql->escape($data["block"])."' WHERE `Thesis_ID`=".$row["Thesis_ID"]);
	if ( $update !== FALSE ){
	  plog($log,"Successfully updated thesis abstract: " . $row["Thesis_ID"]);
	  return TRUE;
	}
	else {
	  plog($log,"Error when updating thesis abstract: " . $row["Thesis_ID"] . " Error: " . $sql->get_error());
	  return FALSE;
	}
      }
    }
    else {
      // Insert it!
      $insert = $sql->query("INSERT INTO `Thesis` (`Name`,`Abstract`,`Year`,`Department`,`School`,`University`,`Blob`,`Author`,`Advisor1`) VALUES ('"
			   .$sql->escape($data["name"])."','"
		           .$sql->escape($data["block"])."','"
		           .$sql->escape($data["year"])."','"
		           .$sql->escape($data["department"])."','"
		           .$sql->escape($data["school"])."','"
		           .$sql->escape($data["university"])."','"
		           .$sql->escape($data["name"]." "
		           .$sql->escape($data["block"]))."','"
		           .$sql->escape($data["author"])."','"
		           .$sql->escape($data["faculty"])."')");
      if ( $insert !== FALSE ){
	plog($log,"Inserted new thesis: " . $data["name"]);
	return TRUE;
      }
      else {
	plog($log,"Error occurred when inserting new thesis: " . $data["name"]);
	return FALSE;
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

	// Backup the info
	backup_information($row,"Funding",$backup);
	if ( stripos($blob,$row["Abstract"]) === FALSE ){
	  $sql->query("UPDATE `Funding` SET `Abstract`='".$sql->escape($blob." ".$row["Abstract"])."' WHERE `Funding_ID`=".$row["Funding_ID"]);
	  plog($log,"Error occurred when updating funding: " . $row["Funding_ID"] . " Error: " . $sql->get_error());
	  return FALSE;
	}
	$update = $sql->query("UPDATE `Funding` SET `Abstract`='".$sql->escape($data["block"]).", `Link`='".$sql->escape($data["link"])."' WHERE `Funding_ID`=".$row["Funding_ID"]);
	if ( $update === FALSE ){
	  plog($log,"Failed to update funding abstract: " . $row["Funding_ID"]. " Error: " . $sql->get_error());
	  return FALSE;
	}
	else {
	  return TRUE;
	}
      }
    }
    else {
      // Insert it!
      $insert = $sql->query("INSERT INTO `Funding` (`Name`,`Abstract`,`Department`,`School`,`University`,`FirstNamePI`,`Link`) VALUES ('".
		$sql->escape($data["name"])."','".
		$sql->escape($data["block"])."','".
		$sql->escape($data["department"])."','".
		$sql->escape($data["school"])."','".
		$sql->escape($data["university"])."','".
		$sql->escape($data["manager"])."','".
		$sql->escape($data["link"])."')");
      if ( $insert !== FALSE ){
	plog($log,"Inserted new thesis: " . $data["name"]);
	return TRUE;
      }
      else {
	plog($log,"Error occurred when inserting new thesis: " . $data["name"]);
	return FALSE;
      }
    }
  }
}
function update_database_grant($data, $log, $sql, $backup){
  $data["university"] = preg_replace("/\s/","_",$data["university"]);
  // Check for previous import
  $result = $sql->query("SELECT * FROM `Grant` WHERE `Name`='".$sql->escape(trim($data["name"]))."' AND `University`='".$sql->escape($data["university"])."'");
  if ( $result === FALSE ){
    plog($log,"Error occurred when selecting grant: " . $data["name"]);
    return FALSE;
  }
  else {
    if ( mysqli_num_rows($result) > 0 ){
      while ( $row = mysqli_fetch_array($result) ){
	$blob = $row["Blob"];

	// Backup the info
	backup_information($row,"Grant",$backup);

	// Update the blob
	if ( stripos($blob,$row["Description"]) === FALSE ){
	  $sql->query("UPDATE `Grant` SET `Blob`='".$sql->escape($blob." ".$row["Description"])."' WHERE `Grant_ID`=".$row["Grant_ID"]);
	  plog($log,"Error occurred when updating grant: " . $row["Grant_ID"] . " Error: " . $sql->get_error());
	}
	$update = $sql->query("UPDATE `Grant` SET `Description`='".
		  $sql->escape($data["block"])."', `Link`='".
		  $sql->escape($data["link"]) ."', `Email`='".
		  $sql->escape($data["email"])."',  `Sponsor`='".
		  $sql->escape($data["sponsor"])."'WHERE `Grant_ID`=".$row["Grant_ID"]);
	if ( $update === FALSE )
	  plog($log,"Failed to update grant description: " . $row["Grant_ID"]. " Error: " . $sql->get_error());
	else 
	  plog($log,"Updated grant: " . $row["Grant_ID"]);
      }
    }
    else {
      // Insert it!
      $insert = $sql->query("INSERT INTO `Grant` (`Name`,`Description`,`Email`,`University`,`Sponsor`,`Link`) VALUES ('".
		$sql->escape($data["name"])."','".
		$sql->escape($data["block"])."','".
		$sql->escape($data["email"])."','".
		$sql->escape($data["university"])."','".
		$sql->escape($data["sponsor"])."','".
		$sql->escape($data["link"])."')");
      if ( $insert !== FALSE )
	plog($log,"Inserted new grant: " . $data["name"]);
      else
	plog($log,"Error occurred when inserting new grant: " . $data["name"]);
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
  global $manual;
  // All data returned from Import.IO may OR may not be an array. ALWAYS check for an array
  $previouslyImported = previousImport($data["name"],$data,"Advisor",$sql);

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
  
  if ( $previouslyImported !== FALSE ){
    // Update the information
    if ( updateAdvisor($import,$sql->get_con(),$previouslyImported[0]) === FALSE ){ // Send test which contains the advisor's ID
      plog($log,"Error occurred when updating advisor information: " . mysqli_error($sql->get_con())." Name: ".$import["name"]);
      $manual[] = $import;
    }
  }
  elseif ( $previouslyImported == FALSE ){
      if ( insertAdvisor($import,$sql->get_con()) === FALSE )
	plog($log,"Error occurred when inserting advisor: " . mysqli_error($sql->get_con()));
  }
}
function updateAdvisor($data,$con,$old_data){
  global $log_file;
  $block      = $data["block"];
  
  // If the new header is empty, keep the old header
  preg_match("/\S/",$data["header"],$chars);
  if ( count($chars) == 0 )
    $header = $old_data["Header"];
  else 
    $header = $data["header"];    
  
  // If the new info is empty, keep the old info!
  preg_match("/\S/",$data["info"],$chars);
  if ( count($chars) == 0 )
    $info = $old_data["Info"];
  else 
    $info = $data["info"];

  $department = str_replace("_"," ",$data["department"]);
  $school     = str_replace("_"," ",$data["school"]);
  $select = "SELECT * FROM `Advisor` WHERE `Advisor_ID`=".$old_data["Advisor_ID"];
  $result = mysqli_query($con,$select);
  if ( $result === FALSE ){
    plog($log_file,"Failed to update advisor");
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
    // Check for the old information in the blob
    if ( stripos($blob,$old_data["Block"]) === FALSE )
      $blob .= " ".$old_data["Block"];

    // Check if the header is in the blob
    if ( stripos($blob,$old_data["Header"]) === FALSE )
      $blob .= " ".$old_data["Header"];
    
    // Check if info is in the blob
    if ( stripos($blob,$old_data["Info"]) === FALSE )
      $blob .= " ".$old_data["Info"];

    // Safe-ify the data
    $block      = mysqli_real_escape_string($con,$block);
    $info       = mysqli_real_escape_string($con,$info);
    $header     = mysqli_real_escape_string($con,$header);
    $blob       = mysqli_real_escape_string($con,$blob);
    $department = mysqli_real_escape_string($con,$department);
    $school     = mysqli_real_escape_string($con,$school);

    if ( !isset($data["link"]) ){
      if ( isset($old_data["link"]) ){
	$data["link"] = $old_data["link"];
      }
      else {
	$data["link"] = "";
      }
    }    
    if ( !isset($data["picture"]) ){
      $data["picture"] = "";
    }    

    // Update the database!
    $update = "UPDATE `Advisor` SET `School`='$school', 
                                    `Department`='$department', 
                                    `Header`='$header', 
                                    `Blob`='$blob'";
    if ( $data["link"] != "" )
      $update .= ",`Link`=>'".mysqli_real_escape_string($con,$data["link"])."'";

    $update .= ",`Block`='$block',`Info`='$info',`University`='".$old_data["University"]."',`Scraped_Level`='Secondary'";
    if ( $data["picture"] != "" )
      $update .= ",`Picture`='".$data["picture"]."'";

    $update .= " WHERE `Advisor_ID`=".$old_data["Advisor_ID"];
    if ( mysqli_query($con,$update) === FALSE ){
      plog($log_file,"Error when updating advisor ".$old_data["Advisor_ID"].": " . mysqli_error($con));
      return false;
    }
    else {
      plog($log_file,"Successfully updated advisor: " . $old_data["Advisor_ID"]);
      return true;
    }
  }
}
function insertAdvisor($data,$con){
  global $log_file;
  $department = mysqli_real_escape_string($con,json_encode($data["department"],JSON_FORCE_OBJECT));
  $university = mysqli_real_escape_string($con,$data["university"]);
  $school     = mysqli_real_escape_string($con,json_encode($data["school"],JSON_FORCE_OBJECT));
  $block      = mysqli_real_escape_string($con,$data["block"]);
  $header     = mysqli_real_escape_string($con,$data["header"]);

  $insert  = "INSERT INTO `Advisor` (`Name`,`University`,`School`,`Department`,`Picture`,`Block`,";
  $insert .= "`Header`,`Scraped_Level`) VALUES (";
  $insert .= "'".mysqli_real_escape_string($con,$data["name"])."','$university','$school','$department','".mysqli_real_escape_string($con,$data["picture"])."','$block',";
  $insert .= "'$header','Secondary')";
  
  if ( runSQL($con,$insert) === FALSE ){
    plog($log_file,"Failed to insert advisor");
  }
  else {
    plog($log_file,"Successfully inserted advisor");
  }
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
