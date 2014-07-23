<?
session_start();
// Constants
$INCORRECT_CV_TYPE      = 1;
$INCORRECT_IMG_TYPE     = 2;
$IMG_FILE_NOT_FOUND     = 3;
$CV_FILE_NOT_FOUND      = 4;

$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
if(mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
}
	
$name           = safeString($_POST["Name"]);
$profileImage   = $_FILES["profile_image"];
$cv             = $_FILES["cv"];
$school         = trim(safeString($_POST["School"]));
$department     = trim(safeString($_POST["Department"]));
$research       = safeString($_POST["description"]);
$linkedIn       = safeString($_POST["linkedin"]);
	
if ( $profileImage["name"] != "" ){
  if ( $profileImage["error"] == 0 ){
    $fileTypeCheck = stripos($profileImage["type"],"image");
    preg_match("/\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/",$profileImage["name"],$fileExtCheck);
    
    if ( $fileTypeCheck !== false && count($fileExtCheck) > 0 ){
      $profImage = makeFileName($fileExtCheck[0],"/home/svetlana/www/user-profile-images/");
      move_uploaded_file($profileImage["tmp_name"],$profImage);
    }
    else {
      $_SESSION["edit_profile_error"] = "Improper image type";
      header("Location: ../profile.php");    
      exit();
    }
  }
  else {
    $_SESSION["edit_profile_error"] = "Unknown error: cannot find image";
    header("Location: ../profile.php");    
    exit();    
  }
}
if ( $cv["name"] != "" ){
  if ( $cv["error"] == 0 ){
    $fileTypeCheck = stripos($cv["type"],"pdf");
    preg_match("/\.pdf$/",$cv["name"],$fileExtCheck);
    
    if ( $fileTypeCheck !== false && count($fileExtCheck) > 0 ){
      $cvName = makeFileName($fileExtCheck[0],"/home/svetlana/www/user-outlines/");
      move_uploaded_file($cv["tmp_name"],$cvName);
      $jsonNames = json_decode(file_get_contents("/user-outlines/names.json"),true);
      $jsonNames[$cvName] = $cv["name"];
      file_put_contents("/user-outlines/names.json",json_encode($jsonNames));
    }
    else {
      $_SESSION["edit_profile_error"] = "Improper CV type";
      header("Location: ../profile.php");    
      exit();          
    }
  }
  else {
    $_SESSION["edit_profile_error"] = "Unknown error: cannot find CV";
    header("Location: ../profile.php");    
    exit();    
  }
}

$sql = "UPDATE Users 
			SET Name='".$name."', School='".mega_trim($school)."', Department='".mega_trim($department)."'";
if ( $research != "" ){
  $sql .= ", `Interests`='".trim($research)."'";
}
if ( $linkedin != "" ){
  $sql .= ", `LinkedIn`=".$linkedin."'";
}
if ( $profImage != "" ){
  $sql .= ", `Profile_Image`='$profImage'";
}
if ( $cvName != "" ){
  $sql .= ", `Outlines`='$cvName'";
}
$sql .= " WHERE Email='$_SESSION[email]'";

if (!mysqli_query($con,$sql))
  echo mysqli_error($con);

$_SESSION['research'] = $_POST["research"];	

mysqli_close($con);

header("Location: ../profile.php");
exit();

function makeFileName($ext,$dest){
  $fname = $dest.uniqid().$ext;
  if ( file_exists($fname) === true ){
    return makeFileName($ext,$dest);
  }
  return $fname;
}
function safeString($str){
  global $con;
  return mysqli_real_escape_string($con,$str);
}
function mega_trim($str){
	return preg_replace("/^[\s\t\n]{0,}/","",preg_replace("/[\s\t\n]{0,}$/","",$str));
}
?>
