<?php
/***
 * This script monitors the amount of memory being used by a script. It will send warnings when it reaches developer-specified limits *
 * Copyright: Joseph Stone
 */
class MemoryMonitor {
  private $mem_max;
  private $mem_use;
  private $mem_percent;
  function __construct(){
    $this->mem_max = intval(str_replace("M","",ini_get('memory_limit')),10)*1000000;
    $this->mem_use = memory_get_usage();
    $this->mem_percent = $this->mem_use/$this->mem_max;
  }
  public function get_usage(){
    $this->mem_use = memory_get_usage();
    $this->mem_percent = $this->mem_use/$this->mem_max;
    $usage = array("Usage"=>$this->mem_use,"Max"=>$this->mem_max,"Percent"=>$this->mem_percent);
    return $usage;
  }
}
?>
