<?php
ini_set("auto_detect_line_endings", "1");
require("/home/svetlana/www/beta-code/backend/lib.php");
require("/home/svetlana/www/beta-code/backend/crawler/update_database.php");

$sql = new SQL();
$fix = new NameFixer();

$categories = array(
  "Advisor" => array("University","Block","Picture","Department","School","Name","Header","Email"),
  "Course" => array("University","School","Department","Name","Block","Faculty"),
  "Thesis" => array("University","School","Department","Name","Title","Abstract","Advisor"),
  "Grant" => array("Name","Info","Link","Manager","University","School","Department"),
);
	
$rootpath = './csv/';
$fileinfos = new RecursiveIteratorIterator(
  new RecursiveDirectoryIterator($rootpath)
);
foreach($fileinfos as $pathname => $fileinfo) {
  if (!$fileinfo->isFile()) continue;
  // Read the file info an array
  $f = fopen($pathname,"r+");
  if ( $f ){
    $titles = fgetcsv($f);
    print_r($titles);
    // Find the column with the type of resource
    $typeCol = -1;
    foreach ($titles as $index=>$title){
      if ($title == "Type")
	$typeCol = $index;
    }
    $sql->connect("svetlana_Total");
    while ( $data = fgetcsv($f) ){
      if ( $typeCol == -1 )
	$type = "Advisor";
      else
	$type = fixType(strtoupper($data[$typeCol]));

      foreach ($data as $index=>$value){
	switch ($titles[$index]){
	    case "University":{
	      $university = $value;
	      break;
	    }	    
	    case "School":{
	      $school = $value;
	      break;
	    }
	    case "Department":{
	      $department = $value;
	      break;
	    }
	    case "Block":{
	      $block = $value;
	      break;
	    }
	    case "Info":{
	      $info = $value;
	      break;
	    }
	    case "Header":{
	      $header = $value;
	      break;
	    }
	    case "Picture":{
	      $picture = $value;
	      break;
	    }
	    case "Name":{
	      $name = $value;
	      break;
	    }
	}
      }
      // Standardize the name
      $name = $fix->properize($name);

      // Replace all line breaks with <br/> tags
      $block = preg_replace("/[\r\n]/","<br/>",trim($block));
      $info = preg_replace("/[\r\n]/","<br/>",trim($info));
      $header = preg_replace("/[\r\n]/","<br/>",trim($header));

      // Make the department and school a JSON object
      $department = json_encode(array(trim($department)));
      $school = json_encode(array(trim($school)));

      // Replace spaces with underscores in the university name
      $university = str_replace(" ","_",trim($university));

      echo $department."<br/>$university<br/>";

      $university = "";
      $department = "";
      $school = "";
      $picture = "";
      $header = "";
      $block = "";
      $info = "";
    }
    $sql->close();
  }
  fclose($f);
}

function fixType($type){
  if ( $type == "Advisors" || $type == "advisors" )
    return "Advisor";
  else if ( $type == "Courses" || $type == "courses" ){
    return "Course";
  }
  else if ( $type == "Theses" || $type == "theses" ){
    return "Thesis";
  }
  else if ( $type == "Grants" || $type == "grants" || $type == "Grant" || $type == "grant" ){
    return "Funding";
  }
  else
    return $type; // It was actually correct??
}
?>
