<?php

// Check for simple_html_dom
if ( !function_exists('str_get_html') ){
  if ( file_exists('simple_html_dom.php') )
    include('simple_html_dom.php');
  else
    die('simple_html_dom is required for this parser to work. Please either include the library in your file or include the simple_html_dom.php script in your folder.');
}

class Parser {
  // NOTE: Rows are indexed starting at 1 NOT 0
  private $html = null;
  private $sheets = array();
  private $sheets_keys = array();
  private $headers = array();
  public  $current_sheet;
  public  $matrix = array();
  public  $header_row = -1;
  public  $error_msg = "";

  const ELEMENT_DNE = -1;

  private function load_spreadsheets(){
    // Get all of the sheets and their titles
    foreach ($this->html->find("#sheet-menu") as $sheet_menu){
      foreach ($sheet_menu->find("li") as $li){
	$id   = str_replace("sheet-button-","",$li->id);
	$name = $li->plaintext;
	$this->sheets[$name] = $id;
      }
    }
    $this->sheets_keys = array_keys($this->sheets);
    return $this;
  }
  private function parse_rows(){
    // If a sheet hasn't been selected, then select the first sheet
    if ( !$this->current_sheet )
      $this->get_sheet(0);
    $rows = $this->current_sheet->find("tr");
    $this->parse_columns($rows);
    return $this;
  }
  private function parse_columns($rows){
    $headers = array();
    $data    = array();
    $count   = 0;
    if ( $this->header_row > -1 ){
      foreach ($rows as $tr){
	if ( $count == $this->header_row ){
	  foreach ($tr->find("td") as $td){
	    $headers[] = $td->plaintext;
	  }
	  break;
	}
	$count++;
      }
    }
    $this->headers = $headers;
    // Now get all of the data
    foreach ($rows as $tr){
      $this->matrix[] = array();
      $index = count($this->matrix)-1;
      $col_count = 0;
      foreach ($tr->find("td") as $td){
	$this->matrix[$index][$col_count] = array("text"=>$td->plaintext,"html"=>$td);
	if ( isset($headers[$col_count]) ){
	  $this->matrix[$index][$headers[$col_count]] = &$this->matrix[$index][$col_count];
	}
	$col_count++;
      }
    }
    array_splice($this->matrix,$this->header_row,1);
    array_splice($this->matrix,0,1);
    return $this;
  }
  private function error($code,$data){
    switch ($code){
	case Parser::ELEMENT_DNE : {
	  $error_msg = "Sheet '$data' does not exist";
	}
	case Parser::INVALID_HEADER : {
	  $error_msg = "'$data' is not a valid column header for the current sheet.";
	}
    }
    return $code;
  }
  // Getters
  public function get_sheet_name($n){
    return $this->sheets_keys[$n];
  }
  public function get_sheets_keys(){
    return $this->sheets_keys;
  }
  public function get_columns_with_header($header,$beg,$end){
    if ( !isset($this->headers[$header]) )
      return $this->error(Parser::INVALID_HEADER,$header);
    
  }
  public function get_sheets(){
    return $this->sheets;
  }
  public function get_headers(){
    return $this->headers;
  }
  public function set_row_as_column_header($n){
    // Row n will contain headers for each column
    if ( $n == 0 )
      $n = 1;
    $this->header_row = $n;
    return $this;
  }
  public function get_sheet($name){
    preg_match("/\S/",$name,$test);
    if ( count($test) == 0 )
      return FALSE;
    unset($this->matrix);
    if ( isset($this->sheets[$name]) )
      $this->current_sheet = $this->html->find("#".$this->sheets[$name],0);
    else if ( $name < count($this->sheets_keys) && isset($this->sheets[$this->sheets_keys[$name]]) ){
      preg_match("/\S/",$this->sheets_keys[$name],$test);
      if ( count($test) == 0 )
	return FALSE;
      preg_match("/\S/",$this->sheets[$this->sheets_keys[$name]],$test);
      if ( count($test) == 0 )
	return FALSE;
      $this->current_sheet = $this->html->find("#".$this->sheets[$this->sheets_keys[$name]],0);
    }
    else
      return $this->error(Parser::ELEMENT_DNE,$name);
    if ( gettype($this->current_sheet) == "object" && method_exists($this->current_sheet,"find") ){
      $this->parse_rows();
      return $this;
    }
    else
      return $this->error(Parser::ELEMENT_DNE,$name);
  }
  public function get_sheet_with_header_row($name,$n){
    if ( $n == 0 )
      $n = 1;
    $this->set_row_as_column_header($n)->get_sheet($name);
    return $this;
  }
  public function get_sheet_titles(){
    return $this->sheets_keys;
  }
  // Prepare the spreadsheet
  public function parse($html){
    if ( is_null($this->html) === FALSE ){
      unset($this->html);
      echo gettype($this->html);exit;
    }
    $this->html = str_get_html($html);
    $this->load_spreadsheets();
    return $this;
  }
  public function parse_url($url){
    $this->html = file_get_html($url);
    $this->load_spreadsheets();
    return $this;
  }
  public function set_html($simple_dom_html){
    $this->html = $simple_dom_html;  
    $this->load_spreadsheets();
    return $this;
  }
}
function disp($var){
  echo "<pre>";
  if ( is_array($var) || is_object($var) )
    print_r($var);
  else if ( is_string($var) )
    echo htmlspecialchars($var);
  else
    echo "Var is of type: ".gettype($var) . " which isn't displayable yet";
  echo "</pre>";
}

?>
