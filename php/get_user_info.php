<?php 

require("/home/svetlana/www/beta-code/backend/lib.php");
session_start();

define("outline_folder","../user-outlines/");
define("profile_images","../user-profile-images/");
define("default_profile_image","../images/LittleAdvisorRed.png");
define("full_path","/home/svetlana/www/");

$descField = array(
  "Advisor"=>"Block",
  "Course" =>"Description",
  "Thesis" =>"Description",
  "Funding"=>"Abstract"
);
$header = array(
  "Advisor"=>"Header",
  "Funding"=>"FirstNamePI",
  "Course"=>"Description",
  "Thesis"=>"Abstract"
);

if ( isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true ){
  $id = $_SESSION["user_id"];
  $con = sql_connect("svetlana_users");
  $res = sql_query($con,"SELECT * FROM `Users` WHERE `User_ID`=".$id);
  $out = array();
  $cvNames = json_decode(file_get_contents("/home/svetlana/www/user-outlines/names.json"),true);
  while ($row = mysqli_fetch_array($res) ){
    $out["name"]  = $row["Name"];
    $out["block"] = $row["Interests"];
    $out["id"]    = $row["User_ID"];
    if ( hasAlpha($row["Department"]) )
      $out["department"] = $row["Department"];
    if ( hasAlpha($row["School"]) )
      $out["school"] = $row["School"];
    if ( hasAlpha($row["University"]) )
      $out["university"] = $row["University"];
    if ( hasAlpha($row["Email"]) )
      $out["email"] = $row["Email"];
    if ( hasAlpha($row["Outlines"]) ){
      $out["cvLink"] = json_decode($row["Outlines"],true);
      if ( $out["cvLink"] == null || !file_exists(full_path.outline_folder.$out["cvLink"]["file"]) ){
	unset($out["cvLink"]);
      }
      else {
	$out["cvName"] = $out["cvLink"]["fname"];
	$out["cvLink"] = outline_folder.$out["cvLink"]["file"];
      }
    }
    if ( hasAlpha($row["Profile_Image"]) ){
      if ( file_exists(profile_images.$row["Profile_Image"]) )
	$out["picture"] = profile_images.$row["Profile_Image"];
      else
	$out["picture"] = default_profile_image;
    }
    else 
      	$out["picture"] = default_profile_image;
  }
  // Get saved resources
  $out["saved"] = array(
    "Advisor"=>array(),
    "Course" =>array(),
    "Thesis" =>array(),
    "Funding"=>array()
  );
  $res = sql_query($con,"SELECT * FROM `Saved` WHERE `Email`='".$out["email"]."'");
  $saveCon = sql_connect("svetlana_Total");
  while ( $row = mysqli_fetch_array($res) ){
    $type = ucfirst($row["Type"]);
    $id   = $row["Item_ID"];
    if ( $id == "" )
      continue;

    // Get the saved resource information
    $savedRes = sql_query($saveCon,"SELECT * FROM `$type` WHERE `".$type."_ID`=".$id);
    $_row = mysqli_fetch_array($savedRes);
    
    // Is the department a JSON string?
    $dept = json_decode($_row["Department"],true);
    if ( $dept == null )
      $dept = $_row["Department"];
    else
      $dept = implode(" - ",$dept);

    // Is the school a JSON string?
    $school = json_decode($_row["School"],true);
    if ( $school == null )
      $school = $_row["School"];
    else
      $school = implode(" - ",$school);
    
    // Is the email a JSON string?
    $email = json_decode($_row["Email"],true);
    if ( $email == null )
      $email = $_row["Email"];
    else
      $email = $email[0];

    // Push all of the resource information to the saved variable
    $out["saved"][$type][] = array(
      "id"         => $_row[$type."_ID"],
      "name"       => $_row["Name"],
      "university" => str_replace("_"," ",$_row["University"]),
      "department" => $dept,
      "school"     => $school,
      "block"      => $_row[$descField[$type]],
      "email"      => $email,
      "description"=> strip_tags($_row[$header[$type]]),
      "picture"    => $_row["Picture"]
    );
  }
  echo json_encode($out);
}
else {
  echo "Not logged in";
}
mysqli_close($con);

function hasAlpha($string){
  preg_match("/\S/",$string,$matches);
  return count($matches) > 0;
}
?>
