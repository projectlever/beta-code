<?php 
require("/home/svetlana/www/beta-code/backend/lib.php");
$text = $_POST["text"];
$id = $_POST["id"];
$con = sql_connect("svetlana_users");
$res = sql_query($con,"UPDATE `Users` SET `Interests`='".sql_escape($con,$text)."' WHERE `User_ID`=".$id);
echo "saved";
?>
