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
function backup_information($data,$type){
  global $backup_file, $progress_file;
  progress($progress_file,"");
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
  file_put_contents($backup_file,json_encode($json));
}
// This function checks to see if the thing (advisor, course, thesis, etc...) has already been imported. If it has, we are going to UPDATE
// rather than INSERT the thing's information into the database
function checkForPreviousImport($name,$info,$type,$con,$sheet_data){
  // Check to see if the advisor is in the database
  $matches = array();
  $nameCheck = runSQL($con,"SELECT * FROM `$type` WHERE `Name` = '".mysqli_real_escape_string($con,trim($name))."'",$sheet_data);
  if ( $nameCheck === FALSE )
    return FALSE;
  if ( mysqli_num_rows($nameCheck) > 0 ){
    while ( $row = mysqli_fetch_array($nameCheck) ){
      if ( stripos($row["University"],str_replace(" ","_",$info["university"])) !== FALSE && stripos($row["School"],$info["school"]) !== FALSE && stripos($row["Department"],$info["department"]) !== FALSE ){
	$matches[] = $row;
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
    $nameCheck = runSQL($con,"SELECT * FROM `$type` WHERE `Name` REGEXP '".mysqli_real_escape_string($con,trim($regex))."'",$sheet_data);
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
    if ( count($matches) == 1 )
      // PERFECT! Let's back up the data and update the SQL with the new information
      backup_information($matches[0],$type);
    else {
      // More than one name...uh-oh may need to check for differences between them or notify a human that there are multiple inputs
      return REPLICAS;
    }
    return $matches[0];
  }
  else
    return INSERT;
}
function update_database_course($data){
  $sheet_data = &$data["sheet_data"];
  $import = array(
    "name"=>"",
    "description"=>"",
    "faculty"=>"",
    "university"=>preg_replace("/\s/","_",trim($data["info"]["university"])),
    "school"=>trim($data["info"]["school"]),
    "department"=>trim($data["info"]["department"])
  );

  $i = 0;
  $iodata = getIOData($data,$sheet_data,$i);
  while ( $iodata !== FALSE ){
    $con = sql_connect();
    if ( $con == FALSE ){
      $curConTry++;
      if ( $curConTry == $maxConTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Failed to connect to sql database when updating course. Skipping."));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    if ( gettype($iodata) != "object" ){
      $curDataTry++;
      if ( $curDataTry == $maxDataTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Course `iodata` variable is not an object! Max attempts reached. Skipping.","data"=>$iodata));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    if ( !isset($iodata->{'Course title'}) ){
      if ( !isset($iodata->{'Name'}) ){
	if ( !isset($iodata->{'name'}) ){
	  $i++;
	  $iodata = getIOData($data,$sheet_data,$i);
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No course title provided!","data"=>$data));
	  continue;
	  $courseName = "";
	}
	else {
	  if ( gettype($iodata->{'name'}) == "array" )
	    $courseName = $iodata->{'name'}[0];
	  else
	    $courseName = $iodata->{'name'};
	}
      }
      else {
	if ( gettype($iodata->{'Name'}) == "array" )
	  $courseName = $iodata->{'Name'}[0];
	else
	  $courseName = $iodata->{'Name'};
      }
    }
    else {
      if ( gettype($iodata->{'Course title'}) == "array" )
	$courseName = $iodata->{'Course title'}[0];
      else
	$courseName = $iodata->{'Course title'};
    }
    
    // Get the professor of the course
    if ( !isset($iodata->{'Faculty'}) ){
      if ( !isset($iodata->{'Professor'}) ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No faculty name provided for the course!","data"=>$data));
	$faculty = "";
      }
      else {
	if ( gettype($iodata->{'Professor'}) == "array" )
	  $faculty = $iodata->{'Professor'}[0];
	else
	  $faculty = $iodata->{'Professor'};
      }
    }
    else {
      if ( gettype($iodata->{'Faculty'}) == "array" )
	$faculty = $iodata->{'Faculty'}[0];
      else
	$faculty = $iodata->{'Faculty'};
    }

    // Get info?
    if ( !isset($iodata->{'info'}) == "array"){
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No description for the course was provided!","data"=>$data));
      $description = "";
    }
    else {
      if ( gettype($iodata->{'info'}) == "array" )
	$description = implode("<br/><br/>",$iodata->{'infp'});
      else
	$description = $iodata->{'info'};
    }

    // Get the course description
    if ( !isset($iodata->{'Description'}) ){
      if ( !isset($iodata->{'Summary'}) ){
	if ( !isset($iodata->{'summary'}) ){
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No description for the course was provided!","data"=>$data));
	  $description = "";
	}
	else {
	  if ( gettype($iodata->{'summary'}) == "array" )
	    $description .= implode("<br/><br/>",$iodata->{'summary'});
	  else
	    $description .= $iodata->{'summary'};
	}
      }
      else {
	if ( gettype($iodata->{'Summary'}) == "array" )
	  $description .= implode("<br/><br/>",$iodata->{"Summary"});
	else
	  $description .= $iodata->{"Summary"};
      }
    }
    else {
      if ( gettype($iodata->{'Description'}) == "array" )
	$description .= implode("<br/><br/>",$iodata->{"Description"});
      else
	$description .= $iodata->{"Description"};
    }
    // Check that the courseName isn't empty
    preg_match("/\S/","",$chars);
    if ( count($chars) == 0 ){
      $i++;
      $iodata = getIOData($data,$sheet_data,$i);
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No course title provided!","data"=>$data));
      continue;
    }
    // Verify that the course isn't already in the database
    $result = runSQL($con,"SELECT * FROM `Course` WHERE `Name`='".trim(mysqli_real_escape_string($con,$courseName))."'",$sheet_data);
    if ( $result === FALSE ){
      $i++;
      $iodata = getIOData($data,$sheet_data,$i);
      continue;
    }
    else {
      if ( mysqli_num_rows($result) > 0 ){
	// The course already exists! Let's check the description. We will keep the bigger one.
	while ( $row = mysqli_fetch_array($result) ){
	  $blob = $row["Blob"];
	  preg_match("/\S/",$row["Blob"],$chars);
	  if ( strlen($description) > strlen($row["Description"]) ){
	    // Add the old blob in!
	    if ( stripos($blob,$row["Description"]) === FALSE ){
	      // Update the blob!
	      $update = runSQL($con,"UPDATE `Course` SET `Blob`='".mysqli_real_escape_string($con,$blob." ".$row["Description"])."' WHERE `Course_ID`=".$row["Course_ID"],$sheet_data);
	      if ( $update !== FALSE )
		logSuccess(array("no previous data"),$import,"Successfully created blob");
	    }
	    // Replace the description! Then exit the function
	    $update = runSQL($con,"UPDATE `Course` SET `Description`='".mysqli_real_escape_string($con,$description)."' WHERE `Course_ID`=".$row["Course_ID"],$sheet_data);
	  }
	  else if ( strlen($description) == strlen($row["Description"]) || count($chars) == 0 ){
	    // Update the blob!
	    $update = runSQL($con,"UPDATE `Course` SET `Blob`='".mysqli_real_escape_string($con,$description." ".$courseName." ".$faculty)."' WHERE `Course_ID`=".$row["Course_ID"],$sheet_data);
	    if ( $update !== FALSE )
	      logSuccess(array("no previous data"),$import,"Successfully created blob");
	  }
	  else {
	    if ( stripos($blob,$description) === FALSE ){
	      // Add the new info to the blob
	      $update = runSQL($con,"UPDATE `Course` SET `Blob`='".mysqli_real_escape_string($con,$blob." ".$description)."' WHERE `Course_ID`=".$row["Course_ID"],$sheet_data);
	      if ( $update !== FALSE )
		logSuccess(array("no previous data"),$import,"Successfully created blob");
	    }
	  }
	}
      }
      else {
	// New entry! So insert it
	$insert = runSQL($con,"INSERT INTO `Course` (`Name`,`Description`,`Faculty`,`School`,`Department`,`University`) VALUES (
'".mysqli_real_escape_string($con,$courseName)."','".mysqli_real_escape_string($con,$description)."','".mysqli_real_escape_string($con,$faculty)."','".mysqli_real_escape_string($con,$import["school"])."','".mysqli_real_escape_string($con,$import["department"])."','".mysqli_real_escape_string($con,$import["university"])."')",$sheet_data);
logSuccess(array("no previous data"),$import,"Successfully inserted course");
      }
    }
    $i++;
    $iodata = getIOData($data,$sheet_data,$i);
    mysqli_close($con);
  }
}
function update_database_thesis($data){
  $sheet_data = &$data["sheet_data"];
  $i = 0;
  $iodata = getIOData($data,$sheet_data,$i);
  while ( $iodata !== FALSE ){
    $con = sql_connect();
    if ( $con == FALSE ){
      $curConTry++;
      if ( $curConTry == $maxConTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Failed to connect to sql database when updating thesis. Skipping."));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    if ( gettype($iodata) != "object" ){
      $curDataTry++;
      if ( $curDataTry == $maxDataTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Thesis `iodata` variable is not an object! Max attempts reached. Skipping.","data"=>$iodata));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    $import = array(
      "name"=>"",
      "description"=>"",
      "faculty"=>"",
      "university"=>preg_replace("/\s/","_",trim($data["info"]["university"])),
      "school"=>trim($data["info"]["school"]),
      "department"=>trim($data["info"]["department"])
    );
    if ( $iodata === FALSE )
      return;
    else {
      if ( isset($iodata->{'Name'}) ){
	$title = $iodata->{'Name'};
	if ( gettype($title) == "array" )
	  $title = $title[0];
      }
      else {
	if ( isset($iodata->{'name'}) ){
	  $title = $iodata->{'name'};
	  if ( gettype($title) == "array" )
	    $title = $title[0];
	}
	else {
	  $title = "";
	}
      }
      
      if ( isset($iodata->{'Author'}) ){
	$author = $iodata->{'Author'};
	if ( gettype($author) == "array" )
	  $author = $author[0];
      }
      else {
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No author provided for project or thesis","data"=>$data));
	$author = "";
      }
      
      if ( isset($iodata->{'Summary'}) ){
	$abstract = $iodata->{'Summary'};
	if ( gettype($abstract) == "array" )
	  $abstract = implode("<br/>",$abstract);
      }
      else {
	if ( isset($iodata->{'summary'}) ){
	  $abstract = $iodata->{'summary'};
	  if ( gettype($abstract) == "array" )
	    $abstract = $abstract[0];
	}
	else {
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No abstract provided for project or thesis","data"=>$data));
	  $abstract = "";
	}
      }
      
      if ( isset($iodata->{'Date'}) ){
	$year = $iodata->{'Date'};
	if ( gettype($year) == "array" )
	  $year = $year[0];
      }
      else {
	if ( isset($iodata->{'date'}) ){
	  $year = $iodata->{'date'};
	  if ( gettype($year) == "array" )
	    $year = $year[0];
	}
	else {
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No date provided for project or thesis","data"=>$data));
	  $year = "";
	}
      }
      
      if ( isset($iodata->{'Link'}) ){
	$link = $iodata->{'Link'};
	if ( gettype($link) == "array" )
	  $link = $link[0];
      }
      else {
	if ( isset($iodata->{'link'}) ){
	  $link = $iodata->{'link'};
	  if ( gettype($link) == "array" )
	    $link = $link[0];
	}
	else {
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No link was provided for project or thesis","data"=>$data));
	  $link = "";
	}
      }
      if ( isset($iodata->{'Faculty'}) ){
	$faculty = $iodata->{'Faculty'};
	if ( gettype($faculty) == "array" )
	  $faculty = implode(",",$faculty);
      }
      else {
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("messages"=>"No faculty name provided for the thesis","data"=>$data));
	$faculty = "";
      }
      
      // Check for department, school, and university
      preg_match("/\S/",$import["university"],$chars);
      if ( count($chars) == 0 ){
	// Check for the info in the scraper
	if ( isset($iodata->{'University'}) ){
	  $import["university"] = $iodata->{'University'};
	}
	else {
	  if ( isset($iodata->{'university'}) )
	    $import["university"] = $iodata->{'university'};
	  else {
	    $i++;
	    $iodata = getIOData($data,$sheet_data,$i);
	    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No university for this thesis was provided!","data"=>$data));
	    continue;
	  }
	}
      }
      preg_match("/\S/",$import["school"],$chars);
      if ( count($chars) == 0 ){
	// Check for the info in the scraper
	if ( isset($iodata->{'School'}) ){
	  $import["school"] = $iodata->{'School'};
	}
	else {
	  if ( isset($iodata->{'school'}) )
	    $import["school"] = $iodata->{'school'};
	  else {
	    $i++;
	    $iodata = getIOData($data,$sheet_data,$i);
	    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No school for this thesis was provided!","data"=>$data));
	    continue;
	  }
	}
      }
      preg_match("/\S/",$import["department"],$chars);
      if ( count($chars) == 0 ){
	// Check for the info in the scraper
	if ( isset($iodata->{'Department'}) ){
	  $import["department"] = $iodata->{'Department'};
	}
	else {
	  if ( isset($iodata->{'department'}) )
	    $import["department"] = $iodata->{'department'};
	  else {
	    $i++;
	    $iodata = getIOData($data,$sheet_data,$i);
	    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No department for this thesis was provided!","data"=>$data));
	    continue;
	  }
	}
      }
      
      // Verify that this thesis hasn't been inserted into the database yet
      $result = runSQL($con,"SELECT * FROM `Thesis` WHERE `Name`='".mysqli_real_escape_string($con,$title)."' AND `University`='".mysqli_real_escape_string($con,$import["university"])."'",$sheet_data);
      if ( $result === FALSE ){
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
	continue;
      }
      else {
	if ( mysqli_num_rows($result) > 0 ){
	  $blob = "";
	  while ( $row = mysqli_fetch_array($result) ){
	    $blob = $row["Blob"];	  
	    $old_abstract = $row["Abstract"];
	    // See if the new abstract is bigger than the old abstract
	    if ( strlen($abstract) > strlen($row["Abstract"]) ){
	    // Replace it!
	      $update = runSQL($con,"UPDATE `Thesis` SET `Abstract`='".mysqli_real_escape_string($con,$abstract)."' WHERE `Thesis_ID`=".$row["Thesis_ID"],$sheet_data);
	      if ( $update !== FALSE )
		logSuccess($row,array($import,"title"=>$title,"abstract"=>$abstract,"year"=>$year,"link"=>$link),"Successfully updated Thesis");
	    }
	    else {
	      // Update the Blob
	      if ( stripos($blob,$abstract) === FALSE ){
		$update = runSQL($con,"UPDATE `Thesis` SET `Blob`='".mysqli_real_escape_string($con,$blob." ".$abstract)."' WHERE `Thesis_ID`=".$row["Thesis_ID"],$sheet_data);
		if ( $update !== FALSE )
		  logSuccess($row,array($import,"title"=>$title,"abstract"=>$abstract,"year"=>$year,"link"=>$link),"Successfully updated Thesis blob");
	      }
	    }
	    // Update the Blob
	    if ( stripos($blob,$old_abstract) === FALSE ){
	      $update = runSQL($con,"UPDATE `Thesis` SET `Blob`='".mysqli_real_escape_string($con,$blob." ".$old_abstract)."' WHERE `Thesis_ID`=".$row["Thesis_ID"],$sheet_data);
	      if ( $update !== FALSE )
		logSuccess($row,array($import,"title"=>$title,"abstract"=>$abstract,"year"=>$year,"link"=>$link),"Successfully updated Thesis blob");
	    }
	  }
	}
	else {
	  // Insert it!
	  $insert = runSQL($con,"INSERT INTO `Thesis` (`Name`,`Abstract`,`Year`,`Link`,`Department`,`School`,`University`,`Blob`,`Author`,`Advisor1`) VALUES ('".mysqli_real_escape_string($con,$title)."','".mysqli_real_escape_string($con,$abstract)."','".mysqli_real_escape_string($con,$year)."','".mysqli_real_escape_string($con,$link)."','".mysqli_real_escape_string($con,$import["department"])."','".mysqli_real_escape_string($con,$import["school"])."','".mysqli_real_escape_string($con,$import["university"])."','".mysqli_real_escape_string($con,$title." ".$abstract." ".$year)."','".mysqli_real_escape_string($con,$author)."','".mysqli_real_escape_string($con,$faculty)."')",$sheet_data);
	  if ( $insert !== FALSE )
	    logSuccess(array("no previous data"),array($import,"title"=>$title,"abstract"=>$abstract,"year"=>$year,"link"=>$link),"Successfully inserted Thesis");
	  else
	    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"SQL error during thesis insert","data"=>array($import,"title"=>$title,"abstract"=>$abstract,"year"=>$year,"link"=>$link)));
	}
      }
    }
    $i++;
    $iodata = getIOData($data,$sheet_data,$i);
    mysqli_close($con);
  }
}
function update_database_funding($data){
  $sheet_data = &$data["sheet_data"];  
  $i = 0;
  $maxConTries = 5;
  $curConTry = 0;
  $maxDataTries = 5;
  $curDataTry = 0;
  $iodata = getIOData($data,$sheet_data,$i);
  while ( $iodata !== FALSE ){
    $con = sql_connect();
    if ( $con == FALSE ){
      $curConTry++;
      if ( $curConTry == $maxConTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Failed to connect to sql database when updating funding. Skipping."));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    if ( gettype($iodata) != "object" ){
      $curDataTry++;
      if ( $curDataTry == $maxDataTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Funding `iodata` variable is not an object! Max attempts reached. Skipping.","data"=>$iodata));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    $import = array(
      "name"=>"",
      "description"=>"",
      "faculty"=>"",
      "university"=>preg_replace("/\s/","_",trim($data["info"]["university"])),
      "school"=>trim($data["info"]["school"]),
      "department"=>trim($data["info"]["department"])
    );
    if ( isset($iodata->{'Name'}) ){
      $name = $iodata->{'Name'};
    }
    if ( isset($iodata->{'Info'}) ){
      $info = $iodata->{'Info'};
    }
    if ( isset($iodata->{'Link'}) ){
      $link = $iodata->{'Link'};
    }
    if ( isset($iodata->{'Manager'}) ){
      $manager = $iodata->{'Manager'};
    }
    // Check for department, school, and university
    preg_match("/\S/",$import["university"],$chars);
    if ( count($chars) == 0 ){
      // Check for the info in the scraper
      if ( isset($iodata->{'University'}) ){
	$import["university"] = $iodata->{'University'};
      }
      else {
	if ( isset($iodata->{'university'}) )
	  $import["university"] = $iodata->{'university'};
	else {
	  $i++;
	  $iodata = getIOData($data,$sheet_data,$i);
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No university for this thesis was provided!","data"=>$data));
	  continue;
	}
      }
    }
    preg_match("/\S/",$import["school"],$chars);
    if ( count($chars) == 0 ){
      // Check for the info in the scraper
      if ( isset($iodata->{'School'}) ){
	$import["school"] = $iodata->{'School'};
	}
      else {
	if ( isset($iodata->{'school'}) )
	  $import["school"] = $iodata->{'school'};
	else {
	  $i++;
	  $iodata = getIOData($data,$sheet_data,$i);
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No school for this thesis was provided!","data"=>$data));
	  continue;
	}
      }
    }
    preg_match("/\S/",$import["department"],$chars);
    if ( count($chars) == 0 ){
      // Check for the info in the scraper
      if ( isset($iodata->{'Department'}) ){
	$import["department"] = $iodata->{'Department'};
      }
      else {
	if ( isset($iodata->{'department'}) )
	  $import["department"] = $iodata->{'department'};
	else {
	  $i++;
	  $iodata = getIOData($data,$sheet_data,$i);
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No department for this thesis was provided!","data"=>$data));
	  continue;
	}
      }
    }
    // Check for previous import
    $result = runSQL($con,"SELECT * FROM `Funding` WHERE `Name`='".mysqli_real_escape_string($con,trim($name))."' AND `University`='".mysqli_real_escape_string($con,$import["university"])."'",$sheet_data);
    if ( $result === FALSE ){
      $i++;
      $iodata = getIOData($data,$sheet_data,$i);
      continue;
    }
    else {
      if ( mysqli_num_rows($result) > 0 ){
	while ( $row = mysqli_fetch_array($result) ){
	  $blob = $row["Blob"];
	  // Keep the larger abstract ($info)
	  if ( strlen($info) > strlen($row["Abstract"]) ){
	    if ( stripos($blob,$row["Abstract"]) === FALSE ){
	      runSQL($con,"UPDATE `Funding` SET `Abstract`='".mysqli_real_escape_string($con,$blob." ".$row["Abstract"])."' WHERE `Funding_ID`=".$row["Funding_ID"],$sheet_data);
	      logSuccess(array(),$data,"Updated Funding blob");
	    }
	    $update = runSQL($con,"UPDATE `Funding` SET `Abstract`='".mysqli_real_escape_string($con,$info)."' WHERE `Funding_ID`=".$row["Funding_ID"],$sheet_name);
	  }
	  else if ( strlen($info) == strlen($row["Abstract"]) || strlen($info) < strlen($row["Abstract"]) ){
	    if ( stripos($blob,$info) === FALSE ){
	      runSQL($con,"UPDATE `Funding` SET `Abstract`='".mysqli_real_escape_string($con,$blob." ".$info)."' WHERE `Funding_ID`=".$row["Funding_ID"],$sheet_data);
	      logSuccess(array(),$data,"Updated Funding blob");
	    }
	  }
	}
      }
      else {
	// Insert it!
	$insert = runSQL($con,"INSERT INTO `Thesis` (`Name`,`Abstract`,`Link`,`Department`,`School`,`University`,`FirstNamePI`) VALUES ('".mysqli_real_escape_string($con,$name)."','".mysqli_real_escape_string($con,$info)."','".mysqli_real_escape_string($con,$link)."','".mysqli_real_escape_string($con,$import["department"])."','".mysqli_real_escape_string($con,$import["school"])."','".mysqli_real_escape_string($con,$import["university"])."','".mysqli_real_escape_string($con,$manager)."')",$sheet_data);
      }
    }
    $i++;
    $iodata = getIOData($data,$sheet_data,$i);
    mysqli_close($con);
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
function update_database_advisor($data){
  // All data returned from Import.IO may OR may not be an array. ALWAYS check for an array

  // Create a fixer object to normalize the name
  $fixer = new NameFixer();

  // Prepare data
  $sheet_data = &$data["sheet_data"];
  $import = array(
    "name"=>"",
    "block"=>"",
    "header"=>"",
    "picture"=>"",
    "university"=>preg_replace("/\s/","_",trim($data["info"]["university"])),
    "school"=>preg_replace("/\s/","_",trim($data["info"]["school"])),
    "department"=>preg_replace("/\s/","_",trim($data["info"]["department"]))
  );
  
  $i = 0;
  $iodata = getIOData($data,$sheet_data,$i);
  while ( $iodata !== FALSE ){
    $con = sql_connect();
    if ( $con == FALSE ){
      $curConTry++;
      if ( $curConTry == $maxConTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Failed to connect to sql database when updating advisor. Skipping."));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    if ( gettype($iodata) != "object" ){
      $curDataTry++;
      if ( $curDataTry == $maxDataTries ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Advisor `iodata` variable is not an object! Max attempts reached. Skipping.","data"=>$iodata));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
      }
      continue;
    }
    // Get the advisor's name
    if ( !isset($iodata->{'name'}) ){
      if ( !isset($iodata->{'Name'}) ){
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"No 'name' was returned with Import.IO object.","webpage"=>$data["webpage"],"result"=>$data));
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
	continue;
      }
      else {
	if ( gettype($iodata->{'Name'}) == "array" ){
	  $i++;
	  $iodata = getIOData($data,$sheet_data,$i);
	  report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"More than one possible name!","names"=>$iodata->{'Name'}));
	  continue;
	}
	else
	  $name = $fixer->properize($iodata->{'name'});
      }
    }
    else {
      if ( gettype($iodata->{'name'}) == "array" ){
	$i++;
	$iodata = getIOData($data,$sheet_data,$i);
	report("incomplete",$sheet_data["sheet-name"],
	       ($sheet_data["row"]+2),array("message"=>"More than one possible name!",
					    "names"=>$iodata));
	continue;
      }
      else 
	$name = $fixer->properize($iodata->{'name'});
    }
    if ( $name === FALSE ){
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Illegal name. After normalizing the provided name, no characters were left.", "webpage"=>$data["webpage"],"result"=>$data));
      $i++;
      $iodata = getIOData($data,$sheet_data,$i);
      continue;
    }
    $test = checkForPreviousImport($name,$data["info"],'Advisor',$con,$sheet_data);
    if ( $test === FALSE ){
      $i++;
      $iodata = getIOData($data,$sheet_data,$i);
      continue;
    }      
    if ( $test == REPLICAS ){
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"Advisor has multiple entries in the database. Please advise.","result"=>$data));
      $i++;
      $iodata = getIOData($data,$sheet_data,$i);
      continue;
    }
    else if ( $test == INSERT ){
      $action = INSERT;
    }
    else {
      $action = UPDATE;
      // Test will contain the old data
    }
    
    // Get the advisor's image
    if ( isset($iodata->{'picture'}) ){
      if ( gettype($iodata->{'picture'}) == "array" )
	$picture = $iodata->{'picture'}[0];
      else
      $picture = $iodata->{'picture'};
    }
    else {
      if ( isset($iodata->{'Picture'}) ){
	if ( gettype($iodata->{'Picture'}) == "array" )
	  $picture = $iodata->{'Picture'}[0];
	else
	  $picture = $iodata->{'Picture'};
      }
      else
	$picture = "http://projectlever.com/images/LittleAdvisorRed.png";
    }
    
    // Get the info
    if ( isset($iodata->{'info'}) ){
      if ( gettype($iodata->{'info'}) == "array" )
	$info = implode("<br/><br/>",$iodata->{'info'});
      else
	$info = $iodata->{'info'};
    }
    else {
      if ( isset($iodata->{'Info'}) ){
	if ( gettype($iodata->{'Info'}) == "array" )
	  $info = implode("<br/><br/>",$iodata->{'Info'});
	else
	  $info = $iodata->{'Info'};
      }
      else 
	$info = "";
    }
    
    // Get the header
    if ( isset($iodata->{'header'}) ){
      if ( gettype($iodata->{'header'}) == "array" )
	$header = implode(" ",$iodata->{'header'});
      else
	$header = $iodata->{'header'};
    }
    else {
      if ( isset($iodata->{'Header'}) ){
	if ( gettype($iodata->{'Header'}) == "array" )
	  $header = implode(" ",$iodata->{'Header'});
	else
	  $header = $iodata->{'Header'};
      }
      else 
	$header = "";
    }
    
    // Get the block
    if ( isset($iodata->{'block'}) ){
      if ( gettype($iodata->{'block'}) == "array" )
	$block = implode("<br/><br/>",$iodata->{'block'});
      else
	$block = $iodata->{'block'};
    }
    else {
      if ( isset($iodata->{'Block'}) ){
	if ( gettype($iodata->{'Block'}) == "array" )
	  $block = implode("<br/><br/>",$iodata->{'Block'});
	else
	  $block = $iodata->{'Block'};
      }
      else
	$block = "";
    }
    
  
    // Replace header, picture, name, and info in block
    $block = str_replace(array($header,$picture,$name,$info),"",$block);

    // Remove picture and name from the header (if it exists)
    $header = str_replace(array($picture,$name),"",$header);
    
    $import["name"]    = $name;
    $import["block"]   = $block;
    $import["picture"] = $picture;
    $import["header"]  = $header;
    $import["link"]    = $data["pageURL"];
    $import["info"]    = $info;
  
    if ( $action == UPDATE ){
      // Update the information
      // Keep the block that has the most information. This also means that we should include the header that goes with that block
      if ( strlen($import["block"]) > strlen($test["Block"]) ){
	// Keep the new one
	updateAdvisor($import,$con,$test,$sheet_data,$import); // Send test which contains the advisor's ID
      }
      else {
	// Keep the old one but insert the data into the blob for use by the algorithm
	insertDataIntoBlob($import,$con,$test,$sheet_data);
      }
    }
    else {
      if ( $action == INSERT )
	insertAdvisor($import,$con,$sheet_data);
    }
    mysqli_close($con);
    $i++;
    $iodata = getIOData($data,$sheet_data,$i);
  }
}
function insertDataIntoBlob($import,$con,$old_data,$sheet_data){
  $block      = mysqli_real_escape_string($con,$import["block"]);
  $header     = mysqli_real_escape_string($con,$import["header"]);
  // Get the Blob
  $result = runSQL($con,"SELECT * FROM `Advisor` WHERE `Advisor_ID`=".$old_data["Advisor_ID"],$sheet_data);
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

	$update_result = runSQL($con,"UPDATE `Advisor` SET `Blob`='".mysqli_real_escape_string($con,$blob)."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"],$sheet_data);
	if ( $update_result === FALSE ) // If it failed to update, stop here
	  report("incomplete",$sheet_data['sheet-name'],($sheet_data["row"]+2),array("message"=>"Could not insert new block into the blob!","old_data"=>$old_data,"new_data"=>$import));
	else 
	  logSuccess($old_data,$import,"Successfully updated the Blob");
      }
      // Make sure that the link isn't empty
      preg_match("/\S/",$row["Link"],$chars);
      if ( count($chars) === 0 ){
	$update_result = runSQL($con,"UPDATE `Advisor` SET `Link`='".mysqli_real_escape_string($con,$import["link"])."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"],$sheet_data);
	if ( $update_result === FALSE ){
	  report("incomplete",$sheet_data['sheet-name'],($sheet_data["row"]+2),array("message"=>"Failed to update the link","old_data"=>$old_data,"new_data"=>$import,"error"=>mysqli_error($con)));
	}
	else {
	  logSuccess($old_data,$import,"Successfully updated the link.");
	}
      }
    }
    // Otherwise, check to see if this advisor already has the new department, school, and link in their SQL field
    $result = runSQL($con,"SELECT `Department`,`School`,`Link` FROM `Advisor` WHERE `Advisor_ID`=".$old_data["Advisor_ID"],$sheet_data);
    if ( $result === FALSE ){
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"SQL error","error"=>mysqli_error($con)));
    }
    else {
      while ( $row = mysqli_fetch_array($result) ){
	$department = cleanJSONArray($row["Department"],str_replace("_"," ",$import["department"]));
	$school = cleanJSONArray($row["School"],str_replace("_"," ",$import["school"]));
      }
      $result = runSQL($con,"UPDATE `Advisor` SET `Department`='".mysqli_real_escape_string($con,$department)."', `School`='".mysqli_real_escape_string($con,$school)."' WHERE `Advisor_ID`=".$old_data["Advisor_ID"],$sheet_data);
      if ( $result !== FALSE )
	logSuccess($old_data,$import,"Successfully updated schools and departments.");
      else {
	report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"SQL error","error"=>mysqli_error($con)));
      }
      return;
    }
  }
}
function updateAdvisor($data,$con,$old_data,$sheet_data,$import){
  global $updated;
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
    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"MySQL insert error occurred while attempting to update advisor:".$old_data["Advisor_ID"],"old_info"=>$old_data,"new_info"=>$data,"error"=>mysqli_error($con)));
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
    $result = mysqli_query($con,$update);
    if ( $result === FALSE ){
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"MySQL update error occurred while attempting to update advisor:".$old_data["Advisor_ID"],"old_info"=>$old_data,"new_info"=>$data,"error"=>mysqli_error($con)));
    }
    else {
      logSuccess($old_data,$data,"Successful update");
      $updated++;
    }
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
function insertAdvisor($data,$con,$sheet_data){
  global $inserted;
  $department = mysqli_real_escape_string($con,json_encode($data["department"],JSON_FORCE_OBJECT));
  $university = mysqli_real_escape_string($con,$data["university"]);
  $school     = mysqli_real_escape_string($con,json_encode($data["school"],JSON_FORCE_OBJECT));
  $block      = mysqli_real_escape_string($con,$data["block"]);
  $header     = mysqli_real_escape_string($con,$data["header"]);

  $insert  = "INSERT INTO `Advisor` (`Name`,`University`,`School`,`Department`,`Picture`,`Block`,";
  $insert .= "`Header`,`Scraped_Level`) VALUES (";
  $insert .= "'".mysqli_real_escape_string($con,$data["name"])."','$university','$school','$department','".mysqli_real_escape_string($con,$data["picture"])."','$block',";
  $insert .= "'$header','Secondary')";
  
  if ( runSQL($con,$insert,$sheet_data) === FALSE )
    report("incomplete",$sheet_data["sheet-name"],($sheet_name["row"]+2),array("message"=>"Error occurred while inserting a new advisor.","error"=>mysqli_error($con)));
  else {
    logSuccess(array("no previous data"),$data,"Successfully inserted new advisor.");
    $inserted++;
  }
  return;
}
function runSQL($con,$query_string,$sheet_data){
  $result = mysqli_query($con,$query_string);
  if ( !$result ){
    report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"MySQL query error:","error"=>mysqli_error($con)));
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
