<?php
// test
$file = trim(strip_tags($_POST["file"]));
$cur  = $_POST["cur"];
$prev = $_POST["prev"];

$cmd = "git diff HEAD".create_caret($cur)." HEAD".create_caret($prev).
       " ../../$file";
$diff = shell_exec($cmd);
echo $diff;

function create_caret($n){
  $str = "";
  for ($i = 0; $i < $n; $i++){
    $str .= "^";
  }
  return $str;
}
?>
