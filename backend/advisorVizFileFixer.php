<?php 
// This file changes the names of all of the files in advisor_viz so that they have a ".json" extension
$dir = "../../advisor_viz/";
$files = scandir($dir);
for ($i = 0, $n = count($files); $i < $n; $i++){
  $fname = $dir.$files[$i];
  if ( $files[$i] != "." && $files[$i] != ".." && stripos($fname,".json") === FALSE ){
    rename($fname,$fname.".json");
  }
}
echo "complete";
?>
