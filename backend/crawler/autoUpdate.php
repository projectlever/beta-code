<?php

$notify = "stone.ejoseph@gmail.com";

// Turn on error reporting
error_reporting(-1);
ini_set('display_errors', 'On');

// Include the Import.IO API
include('rest.php');
// Include simple_html_dom to easily grab the links
include('simple_html_dom.php');
// Include the google spreadsheet reader
include('parse_google_spreadsheet.php');
// Functions for inserting data
include('update_database.php');
// Helper functions for parsing the information in the spreadsheet
include('parser_functions.php');
// Iclude linkParser to make relative paths into absolute paths
include('/home/svetlana/www/php-lib/parseLink.php');
// Include SQL object
include('/home/svetlana/www/beta-code/backend/sql.php');


$html          = file_get_contents("https://docs.google.com/spreadsheets/d/1kbXatsgaumtitUFQzMisAqvSQ01aHQcu51UYFfRmUhQ/pubhtml");

$log_file      = "./logs/" . date("m_d_y_H_i_s") . "_autocrawl_log.json";
$backup_file   = "./backups/" . date("m_d_y_H_i_s") . "_entry_backup.json";
$manual_file   = "manual_imports.json";
$results_file  = "./results/" . date("m_d_y_H_i_s") . "_autocrawl_results.json";

// Manual imports array
$manual = array();

// Load the parser and get the first sheet
$parser = new Parser();
$parser->parse($html);

// Prepare the SQL object
$sql = new SQL();

// Define the column names
define("Link_Scraper","Link Scraper"); // The extractor or crawler that returns a list of links to be scraped
define("Data_Scraper","Data Scraper"); // The extractor or crawler that returns data from any link being scraped
define("Direct_Link","Direct Link"); // The first link that will be scraped

// Data to track
$total = array(
  "Advisor"=>0,
  "Course"=>0,
  "Thesis"=>0,
  "Funding"=>0
);

// Database columns by type of resource
$categories = array( // The left most element takes priority over the others
  "Advisor"=>array(
    "name"=>array("name/_text"),
    "block"=>array("block"),
    "info"=>array("info","bio"),
    "header"=>array("header"),
    "email"=>array("email"),
    "picture"=>array("picture")
  ),
  "Course"=>array(
    "name"=>array("name/_text","name","course title","course"),
    "block"=>array("description","block"),
    "faculty"=>array("faculty/_text","faculty","professor/_text","professor")
  ),
  "Thesis"=>array(
    "name"=>array("name/_text","name"), // First element takes priority
    "block"=>array("summary","description","block"),
    "advisor"=>array("advisor/_text","advisor"),
    "title"=>array("title/_text","title"),
    "link"=>array("link")
  ),
  "Funding"=>array(
    "name"=>array("name/_text","name","title/_text","title"),
    "block"=>array("block","description","info"),
    "link"=>array("link","Link"),
    "manager"=>array("manager/_text","manager"),
    "co-pi"=>array("co-pi")
  )
);

// Fields that MUST exist, otherwise it will be labelled as a manual import
$mustHave = array(
  "Advisor"=>array(
    "name"=>array("name/_text","name"), // The property name is what the field SHOULD be called, the property value is what may be given
  ),
  "Course"=>array(
    "name"=>array("course","course title","name"),
  ),
  "Thesis"=>array(
    "name"=>array("name/_text","name"), // First element takes priority
  ),
  "Funding"=>array(
    "name"=>array("name/_text","name","title/_text","title"),
  )
);

for ($s = 1, $z = count($parser->get_sheets_keys()); $s < $z; $s++) {
  // The top row should contain the column headers
  $parser->get_sheet_with_header_row($s, 1);

  // Get the first sheet!
  $sheetName = $parser->get_sheet_name($s);

  // Log our progress
  plog($log_file, "Starting with sheet ".($s+1)." of $z: '" . $sheetName . "'\r\n");

  // If there is no sheet name...
  if ($sheetName === FALSE){
    plog("No sheet name provided for sheet #$s\r\n");
    continue;
  }

  // Used to scrape only a specific sheet
  if (stripos($sheetName, "Fordham") === FALSE) continue;

  // Log the total number of rows in the sheet
  plog($log_file,"Number of rows in sheet: ".count($parser->matrix));

  // Loop through each row in the parser's "matrix". 
  // The parser stores the spreadsheet as a multidimensional array with the $parser[0] being a row and $parser[0]['col_name'] being a column in that row
  for ($i = 0, $n = count($parser->matrix); $i < $n; $i++) {
    $row  = $parser->matrix[$i];
    $rowNumber = $i+2;
    $temp = array();

    // Used to insert a certain typ
    if ( $row["Resource Type"]["text"] != "Funding" ) continue;
    
    // Get each column in the row and store it's text value and SimpleHTMLDOM object
    foreach ($row as $col => $data) {
      $temp[$col] = array(
        $data["text"],
        "simple dom"
      );
    }
    plog($log_file, "Retrieved row " . $rowNumber . " which contains " . print_r($temp,TRUE) . "\r\n");

    plog($log_file, "Checking for list of links in column '".Link_Scraper."'\r\n");

    // Get the links scraper
    $link_code = get_code($row[Link_Scraper]["text"]);

    if ( $link_code === FALSE ){
      plog($log_file,"No link scraper found at row $rowNumber in column '" . Link_Scraper. "'\r\n");
    }
    
    // Get the data scraper
    $data_code = get_code($row[Data_Scraper]["text"]);
      
    if ( $data_code === FALSE ){
      plog($log_file,"No data scraper found at row $rowNumber in column '" . Data_Scraper . "'\r\n");
      $manual[] = array("row"=>$rowNumber,"sheet"=>$sheetName);
    }
    echo "----------".$data_code."--------------<br/>";
    // Make sure at least ONE scraper is present
    if ( $data_code === FALSE && $link_code === FALSE ){
      plog($log_file,"Couldn't find any scraper at row $rowNumber in sheet $sheetName. Will run CSV and PDF importer");
      continue;
    }

    // If a link scraper exists, then get all of the links to scrape from the direct link page
    $dir_links = get_direct_link($row);
    if ( $dir_links === FALSE ){
      plog($log_file,"An invalid direct link was found at row $rowNumber in sheet $sheetName. Skipped.\r\n");
      continue;
    }
    plog($log_file,"Obtained direct link from row $rowNumber. Checking for link scraper...\r\n");

    if ( $link_code !== FALSE && $data_code !== FALSE ){
      plog($log_file,"Found link scraper '$link_code'. TODO: get all links from the direct links provided.\r\n");
      // Since this is a link scraper, we will NEED a data scraper
      if ( $data_code === FALSE ){
	plog($log_file,"No data scraper provided! at row $rowNumber in sheet $sheetName. Skipping. \r\n");
	continue;
      }

      $links = array();
      // Get the links from the scraper
      foreach ($dir_links as $dex => $link){

	// Query the data from $link
	$result = query($link_code,array(
	  "webpage/url"=>trim($link)
	),$userGuid,$apiKey);

	// Check to see if Import.IO returned a result object
	if ( isResult($result) === FALSE ){
	  plog($log_file,"No result was returned from Import.IO at row $rowNumber in sheet $sheetName. Error: ".print_r($result,true)."\r\n");
	  $manual[] = array("row"=>$rowNumber,"sheet"=>$sheetName);
	  continue;
	}

	// Loop through the results and get all of the links
	foreach ( $result->{'result'} as $dexx => $data ){
	  if ( isset($data->{'links'}) ){
	    if ( gettype($data->{'links'}) == "array" )
	      $links = array_merge($links,$data->{'links'});
	    else 
	      $links[] = $data->{'links'};
	  }
	}
	$code = $data_code;
      }
    }
    else if ( $data_code !== FALSE ){
      plog($log_file,"No link scraper found but a data scraper was found.\r\n");
      $links = $dir_links;
    }
    else {
      plog($log_file, "This error should never have been thrown! There were no links to begin with and should have 'continued' earlier.\r\n");
      continue;
    }
    plog($log_file,"Retrieved links, now getting data from each link");

    // Get the type of resource
    $type = $row["Resource Type"]["text"];
    if ( stripos($type,"advisor") !== FALSE )
      $type = "Advisor";
    else if ( stripos($type,"course") !== FALSE ){
      $type = "Course";
    }
    else if ( stripos($type,"thes") !== FALSE ){
      $type = "Thesis";
    }
    else if ( stripos($type,"funding") !== FALSE || stripos($type,"grant") !== FALSE ){
      $type = "Funding";
    }
    foreach ($links as $iii => $link){
      echo "<br/>---------------------------------------------------------<br/>";
      // Get IO result
      $result = query($data_code,array(
	"webpage/url"=>$link
      ),$userGuid,$apiKey);

      // Check to see if the result is valid
      if ( isResult($result) === FALSE ){
	plog($log_file,"A non-result object was returned from Import.IO at row $rowNumber in sheet $sheetName. Error: " . print_r($result,true) . "\r\n");
	continue;
      }
      plog($log_file,"Got result from Import.IO");
      // Get the data from the object and store it in the database
      $fields = $categories[$type];
      $data   = array();
      $results = $result->{'results'};

      // Loop through each result (which is an stdClass object)
      foreach ($results as $iiii => $result){
	// Loop through each field name and see if that field exists in the result object
	foreach ($fields as $trueName => $fieldNames){
	  $data[$trueName] = "";
	  for ( $h = count($fieldNames)-1; $h > -1; $h-- ){
	    $fieldName = $fieldNames[$h];
	    if ( isset($result->{$fieldName}) ){
	      $temp = $result->{$fieldName};
	      
	      // Check to see if the result field is an array
	      if ( gettype($temp) == "array" ){
		
		// Now, we only want relevent data. To check for this, we will run some preliminary tests:
		// 1. Check to see if there are any non-white space characters  in the array field
		// 2. Strip the tags and see if there is any text left (this ensures we won't be loading empty elements into the database)
		
		$use = array();
		foreach ($temp as $ind => $iodata){
		  if ( hasChars($iodata) === true ){
		    $use[] = $iodata;
		  }
		}
		if ( $trueName == "name" || $trueName == "header" ){
		  $data[$trueName] = trim(strip_tags(implode(" ",$use)));
		}
		else {
		  $data[$trueName] = trim(strip_tags(implode("<br/>",$use)));
		}
	      }
	      else {
		if ( $trueName == "name" || $trueName == "header" ){
		  $data[$trueName] = trim(strip_tags($result->{$fieldName}));
		}
		else {
		  $data[$trueName] = trim(strip_tags($result->{$fieldName}));
		}
	      }
	    }
	  }
	}
	$data["university"] = $row["University"]["text"];
	$data["school"] = $row["School"]["text"];
	$data["department"] = $row["Department"]["text"];
	plog($log_file,"Got data from result");

	// Now, import the data into the database IF the mustHave fields exist
	$missingField = false;
	foreach ($mustHave[$type] as $trueName => $fieldNames){
	  if ( !isset($data[$trueName]) ){
	      plog($log_file,"Missing '$trueName' at row $rowNumber in sheet '$sheetName'\r\n");
	      $missingField = true;
	      // Mark it as a manual import and skip to the next result
	      $manual[] = array("row"=>$rowNumber,"sheet"=>$sheetName);
	      continue;
	  }
	}
	if ( $missingField === true )
	  continue; // Skip it!

	// Make sure the name isn't blank!
	if ( hasChars($data["name"]) === false ){
	  plog($log_file,"Missing name at row $rowNumber in sheet $sheetName");
	  continue;
	}
	plog($log_file,"Inserting data into database...");
	$sql->connect("svetlana_Total");
	// If they do have the mustHave fields, then prepare the information to be inserted into the database!
	if ( function_exists("update_database_".strtolower($type)) === true )
	  call_user_func_array("update_database_".strtolower($type),array($data,$log_file,$sql,$backup_file));
	$sql->close();
      }
    }
    sleep(5);
  }
}
echo "complete";
mail($notify,"AutoUpdater Scraping","Scraping complete. Report detailed below:");

function hasChars($str){
  preg_match("/\S/",$str,$chars);
  if ( count($chars) > 0 ){
    return true;
  }
  return false;
}
function get_web_page($link, $userGuid, $apiKey, $type, $sheet_data, $code, $info)
{
    if ($type == "Advisors" || $type == "advisors") {
        $type = "advisor";
    } else if ($type == "Courses" || $type == "courses") {
        $type = "course";
    } else if ($type == "Theses" || $type == "theses") {
        $type = "thesis";
    } else if ($type == "Grants" || $type == "grants") {
        $type = "funding";
    }
    $result = query($code, array(
        "webpage/url" => $link
    ), $userGuid, $apiKey);
    // Check for any errors
    if (isset($result->{'error'})) {
        if (stripos($result->{'error'}, '404') !== FALSE) {
            report("incomplete", $sheet_data["sheet-name"], ($sheet_data["row"] + 2), array(
                "message" => "404 not found error returned from Import.IO. Check the link.",
                "error" => $result
            ));
        } else if (stripos($result->{'error'}, '503') !== FALSE) {
            report("incomplete", $sheet_data["sheet-name"], ($sheet_name["row"] + 2), array(
                "message" => "503 forbidden error returned from Import.IO",
                "error" => $result
            ));
        }
        return FALSE;
    }
    
    if (isset($result->{'results'}) && gettype($result->{'results'}) == 'array') {
        if (!function_exists("update_database_" . $type))
            report("incomplete", $sheetName, ($i + 2), array(
                "message" => "No function found for type: $type",
                "result" => $result,
                "crawl_code" => $code,
                "webpage" => $link
            ));
        call_user_func('update_database_' . $type, array(
            "crawler_code" => $code,
            "pageURL" => $result->{'pageUrl'},
            "ioObject" => $result->{'results'},
            "info" => $info,
            "webpage" => $link,
            "sheet_data" => $sheet_data
        ));
        unset($result);
    } else if ($result == null) {
        add_manual_import(array(
            "webpage" => $link,
            "crawler_code" => $code
        ));
    } else
        report("incomplete", $sheetName, ($i + 2), array(
            "result" => $result,
            "crawl_code" => $code,
            "webpage" => $link
        ));
}
function plog($file,$msg){
  echo $msg."<br/>";
  if ( file_exists($file) )
    file_put_contents($file,$msg."\r\n",FILE_APPEND);
  else
    file_put_contents($file,$msg."\r\n");
}
function get_info($row, $sheet_name, $i)
{
    return array(
        "university" => $row["University"]["text"],
        "school" => $row["School"]["text"],
        "department" => $row["Department"]["text"]
    );
}
function get_crawl_code($row, $sheet_name, $i)
{
    global $incomplete, $progress_file;
    preg_match("/\S/", $row["Crawler Link"]["text"], $charsExist);
    if (count($charsExist) > 0) {
        preg_match("/[a-z0-9A-Z\-]{1,}$/", $row["Crawler Link"]["text"], $crawl_code);
        if (count($crawl_code) == 0) {
            report("incomplete", $sheet_name, ($i + 2), "Badly formed crawl link. Code is missing from the end of the link");
            $incomplete++;
            return FALSE;
        } else
            return trim($crawl_code[0]);
    } else {
        progress($progress_file, "No crawl code provided...Using extractor.\r\n");
        report("incomplete", $sheet_name, ($i + 2), "No crawler or extractor links provided. Assume it's a manual import on row " . $i . " for sheet " . $sheet_name);
    }
    return FALSE;
}
function add_manual_import($arr_data)
{
    global $manual, $manual_file, $progress_file;
    $manual[] = $arr_data;
    file_put_contents($manual_file, json_encode($manual));
    progress($progress_file, "Added manual import\r\n");
}
function report($status, $sheet_name, $row, $msg)
{
    global $log, $log_file, $log_count;
    $log[$status][] = array(
        "row" => $row,
        "sheet" => $sheet_name,
        "message" => $msg
    );
    file_put_contents($log_file, json_encode($log));
    $log_count++;
}
function progress($fname, $message)
{
    echo $message . "<br/>";
    file_put_contents($fname, $message, FILE_APPEND);
}
//disp($parser->get_columns_with_header("Import.IO Links","all"));

//disp($mem->get_usage());
?>
