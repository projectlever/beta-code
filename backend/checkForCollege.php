<?php 
require("./lib.php");

$con = sql_connect("svetlana_Total");
$colleges = json_decode($_POST["colleges"],true);

$available = array();
foreach ($colleges as $index=>$college){
  $result = sql_query($con,"SELECT `Advisor_ID` FROM `Advisor` WHERE `University`='".sql_escape($con,str_replace(" ","_",$college))."' LIMIT 1");
  if ( mysqli_num_rows($result) > 0 ){
    $available[] = $college;
  }
}
echo json_encode($available);
?>
