<?php 
// This file creates a file of advisor ids delimited by a new line from the specified schools/universities
require("../lib.php");

$conditions = array(
  "DivinitySchool.txt"=>"`School` NOT LIKE '%Divinity%' AND `University`='Harvard_University'"
);

$con = sql_connect("svetlana_Total");
$out = "";
if ( $con !== FALSE ){
  foreach ($conditions as $fname=>$value){
    $out = "";
    $result = sql_query($con,"SELECT `Advisor_ID` FROM `Advisor` WHERE ".$value);
    while ( $row = mysqli_fetch_array($result) ){
      if ( $row["Advisor_ID"] != "" )
	$out .= $row["Advisor_ID"]."\r\n";
    }
    file_put_contents("./output/".$fname,$out);
  }
  echo "complete";
}
else {
  echo $function_error;
}
?>
