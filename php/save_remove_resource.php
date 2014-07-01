<?php
session_start();
$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
if (mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
}
// Make sure the type isn't blank
preg_match("/\S/",$_POST["type"],$chars);
if ( count($chars) == 0 ){
  echo "Type is empty!! " . $_POST["type"];
  exit;
}
if ($_POST['saved']=="Save"){
  // Check if the resource is already saved
  $sql = "SELECT * FROM `Saved` WHERE `Email`='".$_SESSION["email"] . "' AND `Item_ID`='".$_POST["id"]."'";
  $result = mysqli_query($con,$sql);
  if ( !$result )
    die(mysqli_error($con));
  else {
    if ( mysqli_num_rows($result) > 0 ){
      // The resource already exists. Exit
      echo "resource exists! " . $_POST["id"];
      exit;
    }
  }
  $sql = "INSERT INTO `Saved` (`Email`, `Item_ID`, `Type`) VALUES ('$_SESSION[email]', '$_POST[id]', '$_POST[type]')";
}
else {
  $sql = "DELETE FROM `Saved`
				WHERE `Email` = '$_SESSION[email]'
				AND `Item_ID` = '$_POST[id]'
				AND `Type` = '$_POST[type]'";				
}			
	
if (!mysqli_query($con,$sql))
  die('Error: ' . mysqli_error($con));
else
  echo "success";
mysqli_close($con);	
?>
