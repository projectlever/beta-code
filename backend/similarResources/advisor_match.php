<?php 
/**
 * This script runs the algorithm on each of the advisors blob in the database.
 * Tests will be run strictly for Harvard AND this script should be run on the server, in the background
 */
set_time_limit(100000000000);
error_reporting(E_ERROR);
ini_set('display_errors','On');

require("/home/svetlana/www/beta-code/backend/lib.php");
require("/home/svetlana/www/beta-code/backend/match_algorithm.php");

$progress="/home/svetlana/www/beta-code/backend/similarResources/progress/log_".date("m_d_Y")."_".date("H_i_s").".txt";
$results="/home/svetlana/www/beta-code/backend/similarResources/results/result_".date("m_d_Y")."_".date("H_i_s").".txt";
$con = sql_connect("svetlana_Total");
$res = sql_query($con,"SELECT `Advisor_ID`,`Blob` FROM `Advisor` WHERE `University`='Harvard_University'");

$totalAdvisors = mysqli_num_rows($res);
$completed = 0;
$completedIds = array();

while ( $row = mysqli_fetch_array($res) ){
  $start = time();
  file_put_contents($progress,"Matching advisor #".$row["Advisor_ID"]."...\r\n",FILE_APPEND);
  if ( $row["Similar_Resources"] == "" ){
    $rank = match(sql_escape($con,$row["Blob"]));
    // Remove the first one since it's the person we just searched. A resource always matches 100% to itself
    unset($rank["Advisor"][0]); 
    // Limit the results to 20
    foreach ($rank as $type=>$list){
      $rank[$type] = array_slice($list,0,19);
    }
    file_put_contents($progress,"Successful match. Time: " . ((time()-$start)) . "seconds\r\n",FILE_APPEND);
    sql_query($con,"UPDATE `Advisor` SET `Similar_Resources`='".sql_escape($con,json_encode($rank))."' WHERE `Advisor_ID`=".$row["Advisor_ID"]);
    file_put_contents($progress,"Successfully updated database for advisor #".$row["Advisor_ID"]."\r\n",FILE_APPEND);
    $completedIds[] = $row["Advisor_ID"];
    $completed++;
  }
}
file_put_contents($results,json_encode(array(
  "total_advisors" => $total,
  "completed_advisors" => $completed,
  "completed_ids" => $completedIds
)));
mysqli_close($con);
?>
