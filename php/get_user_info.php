<?php 

require("/home/svetlana/www/beta-code/backend/lib.php");
session_start();

define("outline_folder","../user-outlines/");
define("profile_images","../user-profile-images/");
define("default_profile_image","../images/LittleAdvisorRed.png");
define("full_path","/home/svetlana/www/");

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
    echo json_encode($out);
  }
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
