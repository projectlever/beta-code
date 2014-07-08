<?php
$start = time();
file_put_contents("start_log.txt","start time -> " . date("m/d/y") . " @ " . date("H:i:s"));
// Turn on error reporting
error_reporting(-1);
ini_set('display_errors','On');
set_time_limit(0);
session_start();
if ( (isset($_GET["reset"]) && $_GET["reset"] == "true") || (isset($argv) && $argv[0] == "true") ){
  $_SESSION["sheet"] = 2;
  $_SESSION["row"] = 1;
  // FILE NAMES
  $_SESSION["log_file"] = "./logs/".date("m_d_y_H_i_s")."_autocrawl_log.json";
  $_SESSION["backup_file"] = "./backups/".date("m_d_y_H_i_s")."_entry_backup.json";
  $_SESSION["manual_file"] = "manual_imports.json";
  $_SESSION["results_file"] = "./results/".date("m_d_y_H_i_s")."_autocrawl_results.json";
  
  $html = file_get_contents("https://docs.google.com/spreadsheets/d/1kbXatsgaumtitUFQzMisAqvSQ01aHQcu51UYFfRmUhQ/pubhtml");
  file_put_contents("test_data.txt",$html);
}
else {
  $html = file_get_contents("test_data.txt");
}

$log_file = "./logs/".date("m_d_y_H_i_s")."_autocrawl_log.json";
$backup_file = "./backups/".date("m_d_y_H_i_s")."_entry_backup.json";
$manual_file = "manual_imports.json";
$results_file = "./results/".date("m_d_y_H_i_s")."_autocrawl_results.json";

// Include the Import.IO API
include('rest.php');
// Include simple_html_dom to easily grab the links
include('simple_html_dom.php');
// Include the google spreadsheet reader
include('parse_google_spreadsheet.php');
// Monitor the memory usage
include ('../../php/memory_monitor/monitor.php');
// Functions for inserting data
include ('update_database.php');

// Logging information
$log = array(
  "complete"=>array(),
  "incomplete"=>array()
);
$log_count = 0;

// Manual imports array
$manual = array();

$rows_completed = 0;
//$max_rows = 5;


// We want to monitor how much memory is being used since it's a very large program
$mem = new MemoryMonitor();

// Get the test data
//$html = file_get_contents("test_data.txt");

// Load the parser and get the first sheet
$parser = new Parser();
$parser->parse($html);

$completed_rows = 0;
$incomplete = 0;

for ( $s = 1, $z = count($parser->get_sheets_keys()); $s < $z; $s++ ){
  $parser->get_sheet_with_header_row($s,1);
  $sheetName = $parser->get_sheet_name($s);
  if ( $sheetName === FALSE )
    continue;
  for ( $i = 0, $n = count($parser->matrix); $i < $n; $i++ ){
    $row = $parser->matrix[$i];
    $code = get_extractor_code($row,$sheetName,$i);
    if ( $code !== FALSE && substr_count($code,"-") == 4 ){
      // An extractor link is here, meaning that we need to get the advisor's links from the Direct Link column and then run the Import.IO Link with those pages
      // Get the direct link
      $dir_links = get_direct_link($row,$sheetName,$i);
      if ( $dir_links !== FALSE ){
	$links = array();
	foreach ($dir_links as $index=>$link){
	  $result = query($code, array(
	  "webpage/url"=>$link
	  ), $userGuid, $apiKey);
	  if ( gettype($result) == "object" ){
	    if ( isset($result->{'results'}) ){
	      if ( count($result->{'results'}) == 0 ){
		report("incomplete",$sheetName,($i+2),array("message"=>"Empty result returned from Import.IO!","result"=>$result,"extractor_code"=>$code,"webpage"=>$link));
	      }
	      foreach ($result->{'results'} as $indexx=>$data){
		if ( isset($data->{'link'}) ){
		  if ( gettype($data->{'link'}) == "array" )
		    $links = array_merge($links,$data->{'link'});
	          else
	            $links[] = $data->{'link'};
		}
		else
		  add_manual_import(array("webpage"=>$link,"extractor_code"=>$code,"data"=>$data));
	      }
	    }
	    else {
	      if ( stripos($result->{'errorType'},"found") !== FALSE )
		report("incomplete",$sheetName,($i+2),array("code_name"=>"incorrect GUID","result"=>$result,"extractor_code"=>$code,"webpage"=>$dir_links,"note"=>"Please ensure that correct code is being used. The code in the provided URL may NOT be correct. The absolutely correct code is in the extractor's details page under the GUID label on the right side of the screen. To get to the details page, click the name of the extractor next to the 'eye' icon on the left side of the extractor page."));
	      else
		report("incomplete",$sheetName,($i+2),array("result"=>$result,"extractor_code"=>$code,"webpage"=>$dir_links));
	    }
	  }
	  else {
	    report("incomplete",$sheetName,($row+2),array("message"=>"Result from Import.IO is not an object!","result"=>$result));
	  }
	}
      }
      // Now that we have all of the links, we're going to crawl each one individually and grab the data. Let's figure out the type of resource we're dealing with first
      $code = get_crawl_code($row,$sheetName,$i);
      if ( $code !== FALSE ){
	$type = trim(strtolower($row["Resource Type"]["text"]));
	$info = get_info($row,$sheetName,$i);
	foreach ($links as $index=>$link){
	  get_web_page($link,$userGuid,$apiKey,$type,array("row"=>$i,"sheet-name"=>$sheetName,"results_file"=>$results_file),$code,$info);
	}
      }
    }
    else {
      // Check for a crawler code 
      // This section is usually used for courses, theses, and funding that have all of the information on one page
      $code = get_crawl_code($row,$sheetName,$i);
      if ( $code !== FALSE ){
	$type = trim(strtolower($row["Resource Type"]["text"]));
	$info = get_info($row,$sheetName,$i);
	$links = explode(",",$row["Direct Links"]["text"]);
	// Get link to crawl
	foreach ($links as $index=>$link){
	  get_web_page($link,$userGuid,$apiKey,$type,array("row"=>$i,"sheet-name"=>$sheetName,"results_file"=>$results_file),$code,$info);
	}
      }
      // No extractor code! Which is necessary!!
      report("incomplete",$sheetName,($i+2),array("message"=>"No extractor link provided for scraper. Trying crawler link...","code"=>$code));
    }
    sleep(5);
    echo implode(", ",$mem->get_usage())."<br/>";
  }
  echo "finished inner loop<br/>";
  sleep(5);
  echo implode(", ",$mem->get_usage())."<br/>";
}
if ( $log_count > 0 )
  echo "There are incomplete items. View the <a href='log_viewer.php' target='_blank'>log file</a><br/>";
echo "Remember to do the <a href='manual_import_viewer.php' target='_blank'>manual imports!</a><br/>";
echo "To view the results (including backup) then view the <a href='results_viewer.php' target='_blank'>results file</a><br/>";
echo "Estimated execution time: " . ((time()-$start)/60) . " minutes<br/>";
echo "Memory used: ";
disp($mem->get_usage());

function get_web_page($link,$userGuid,$apiKey,$type,$sheet_data,$code,$info){
  $result = query($code,array(
		  "webpage/url"=>$link
		  ),$userGuid,$apiKey);
  // Check for any errors
  if ( isset($result->{'error'}) ){
    if ( stripos($result->{'error'},'404') !== FALSE ){
      report("incomplete",$sheet_data["sheet-name"],($sheet_data["row"]+2),array("message"=>"404 not found error returned from Import.IO. Check the link.","error"=>$result));
    }
    else if ( stripos($result->{'error'},'503') !== FALSE ){
      report("incomplete",$sheet_data["sheet-name"],($sheet_name["row"]+2),array("message"=>"503 forbidden error returned from Import.IO","error"=>$result));
    }
    return FALSE;
  }

  if ( isset($result->{'results'}) && gettype($result->{'results'}) == 'array' ){
    call_user_func('update_database_'.$type,array("crawler_code"=>$code,"pageURL"=>$result->{'pageUrl'},"ioObject"=>$result->{'results'},"info"=>$info,"webpage"=>$link,"sheet_data"=>$sheet_data));
    unset($result);
  }
  else if ( $result == null ) {
    add_manual_import(array("webpage"=>$link,"crawler_code"=>$code));
  }
  else
    report("incomplete",$sheetName,($i+2),array("result"=>$result,"crawl_code"=>$code,"webpage"=>$link)); 
}
function get_info($row,$sheet_name,$i){
  return array(
    "university"=>$row["University"]["text"],
    "school"=>$row["School"]["text"],
    "department"=>$row["Department"]["text"]
  );
}
function get_extractor_code($row,$sheet_name,$i){
  global $incomplete;
  preg_match("/[a-z0-9A-Z\-]{1,}\z/",$row["Extractor Links"]["text"],$extractor_code);
  if ( count($extractor_code) == 0 ){
    return FALSE;
  }
  else
    return trim($extractor_code[0]);
}
function get_crawl_code($row,$sheet_name,$i){
  global $incomplete;
  preg_match("/\S/",$row["Crawler Link"]["text"],$charsExist);
  if ( count($charsExist) > 0 ){
    preg_match("/[a-z0-9A-Z\-]{1,}$/",$row["Crawler Link"]["text"],$crawl_code);
    if ( count($crawl_code) == 0 ){
      report("incomplete",$sheet_name,($i+2),"Badly formed crawl link. Code is missing from the end of the link");
      $incomplete++;
      return FALSE;
    }
    else
      return trim($crawl_code[0]);
  }
  else {
    report("incomplete",$sheet_name,($i+2),"No crawler or extractor links provided. Assume it's a manual import on row " . $i . " for sheet " . $sheet_name);
    add_manual_import(array("message"=>"No crawler or extractor links provided.","sheet_name"=>$sheet_name,"row"=>$i));
  }
  return FALSE;
}
function get_direct_link($row,$sheet_name,$i){
  global $incomplete;
  $dir_link = $row["Direct Links"]["text"];
  if ( stripos($dir_link,"http") === FALSE ){
    // This resource could not be scraped. Log it
    report("incomplete",$sheet_name,($i+2),"No direct link was provided.");
    $incomplete++;
    return FALSE;
  }
  else 
    return explode(",",$dir_link);
}
function add_manual_import($arr_data){
  global $manual,$manual_file;
  $manual[] = $arr_data;
  file_put_contents($manual_file,json_encode($manual));
}
function report($status,$sheet_name,$row,$msg){
  global $log, $log_file, $log_count;
  $log[$status][] = array(
    "row"=>$row,
    "sheet"=>$sheet_name,
    "message"=>$msg
  );
  file_put_contents($log_file,json_encode($log));
  $log_count++;
}
//disp($parser->get_columns_with_header("Import.IO Links","all"));

//disp($mem->get_usage());
?>