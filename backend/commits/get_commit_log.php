<?php
// test
$commits = shell_exec("git log");
if ( stripos($commits,"commit") !== FALSE ){
  $commits = explode("commit",$commits);
  $data = array();
  for ( $i = 1, $n = count($commits); $i < $n; $i++ ){
    $commit_data = explode("\n",$commits[$i]);
    if ( count($commit_data) < 3 ){
      continue;
    }
    $date = $commit_data[2];
    // Clean the date information
    $date = trim(str_replace("Date:","",$date));
    preg_match("/[0-9]{2}:[0-9]{2}:[0-9]{2}/",$date,$time);
    if ( !empty($time) ){
      $date = trim(preg_replace("/\s*,/",",",str_replace($time[0],",",$date)));
      preg_match("/[\+\-]{1}[0-9]{4}/",$date,$offset);
      if ( !empty($offset) ){
	$time = $time[0] . " " . $offset[0];
	$date = trim(str_replace($offset[0],"",$date));
	$id  = trim($commit_data[0]);
	$author = trim(str_replace("Author:","",$commit_data[1]));
	$email = substr($author,stripos($author,"<"));
	$email = trim(str_replace("<","",str_replace(">","",$email)));
	$author = trim(str_replace("<$email>","",$author));
	$msg = trim($commit_data[4]);
	$data[] = array(
	  "author"=>$author,
	  "date"=>$date,
	  "time"=>$time,
	  "commit_id"=>$id,
	  "commit_msg"=>$msg,
	  "email"=>$email
	);
      }
    }
  }
  echo json_encode($data);
}
else 
  echo $commits;
?>
