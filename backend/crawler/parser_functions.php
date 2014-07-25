<?php 
// Returns the Import.IO code part of a link that goes to an Import.IO extractor
function get_code($import_io_url)
{
  preg_match("/[a-z0-9A-Z\-]{1,}\z/", $import_io_url, $code);
  if (count($code) == 0)
    return FALSE; 
  else
    return trim($code[0]);
}

// Gets the direct link from the given row and checks to make sure that it is a valid link
function get_direct_link($row)
{
  $dir_link = $row["Direct Links"]["text"];
  if (stripos($dir_link, "http") === FALSE) {
    // Link must have an 'http' in it
    return FALSE;
  } 
  else
    return explode(",", $dir_link);
}
// Verifies that the object returned from Import.IO is a result
function isResult($io){
  return isset($io->{'results'});
}
?>
