<?php
require("/home/svetlana/www/beta-code/backend/lib.php");

session_start();
$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
if (mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
}

if ($_POST['saved']=="Save"){
  // Make sure the type isn't blank
  preg_match("/\S/",$_POST["type"],$chars);
  if ( count($chars) == 0 ){
    echo "Type is empty!! " . $_POST["type"];
    exit;
  }
  // Check if the resource is already saved
  $sql = "SELECT * FROM `Saved` WHERE `Email`='".$_SESSION["email"] . "' AND `Item_ID`='".$_POST["id"]."'";
  $result = mysqli_query($con,$sql);
  if ( !$result || $_SESSION['email'] == '' )
    die(mysqli_error($con));
  else {
    if ( mysqli_num_rows($result) > 0 ){
      // The resource already exists. Exit
      echo "resource exists! " . $_POST["id"];
      exit;
    }
  }
  $sql = "INSERT INTO `Saved` (`Email`, `Item_ID`, `Type`) VALUES ('$_SESSION[email]', '$_POST[id]', '$_POST[type]')";
  if (!mysqli_query($con,$sql))
    die('Error: ' . mysqli_error($con));
  else
    echo "success";

}
else {
  if ( isset($_POST['list']) ){
    $list = json_decode($_POST["list"],true);
    if ( $list == null || $_SESSION['email'] == '' )
      die("failed");
    foreach ( $list as $type=>$ids ){
      if ( $type == "advisors" ) $type = "advisor";
      else if ( $type == "courses" ){ $type = "course"; }
      else if ( $type == "theses" ){ $type = "thesis"; }
      else if ( $type == "grants" ){ $type = "funding"; }
      // Remove each id from the database!
      if ( gettype($ids) == "array" && count($ids) > 0 ){
	foreach ( $ids as $index=>$id )
	  sql_query($con,"DELETE FROM `Saved` WHERE `Email`='".$_SESSION["email"]."' AND `Item_ID`=".$id." AND `Type`='".$type."'");
      }
    }
    echo "success";
  }
  else {
    sql_query($con,"DELETE FROM `Saved` WHERE `Email`='".$_SESSION["email"]."' AND `Item_ID`=".$_POST["id"]." AND `Type`='".$_POST["type"]."'");
    echo "success";
  }
}			
	
mysqli_close($con);	
?>
