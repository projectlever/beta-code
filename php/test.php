<pre><?php 
// This script looks for Similar_Resource data in the database for a given id and type
session_start();
if ( !function_exists("sql_connect") )
  require("/home/svetlana/www/beta-code/backend/lib.php");
require("/home/svetlana/www/beta-code/backend/match_algorithm.php");

  $con = sql_connect("svetlana_Total");
    $res = sql_query($con,"SELECT *  FROM `Advisor` WHERE `Advisor_ID`!='' LIMIT 0,1");
    if ( mysqli_num_rows($res) == 0 )
      exit;
    $row = mysqli_fetch_array($res);

print_r($row);
