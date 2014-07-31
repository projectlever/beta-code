<?php 
class SQL {
  private $con;
  private $lastResult;
  private $error;
  public function connect($database){
    $this->con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6",$database);
    if ( !$this->con ){
      $this->error = mysqli_error($this->con);
      return FALSE;
    }
    return $this;
  }
  public function query($sql){
    if ( !$this->con ){
      $this->error = "Must connect to database before running query command.<br/>";
      return FALSE;
    }
    $res = mysqli_query($this->con,$sql);
    if ( !$res ){
      $this->error = mysqli_error($this->con);
      return FALSE;
    }
    $this->lastResult = $res;
    return $res;
  }
  public function escape($str){
    if ( !$this->con ){
      $this->error = "Must connect to a database (SQL::connect) before escaping a message.";
      return FALSE;
    }
    return mysqli_real_escape_string($this->con,$str);
  }
  public function close(){
    if ( !$this->con ){
      $this->error = "Can't close unopened sql variable. Run SQL::connect first.<br/>";
      return FALSE;
    }
    mysqli_close($this->con);
    return TRUE;
  }
  public function get_con(){
    return $this->con;
  }
  public function get_last_result(){
    return $this->lastResult;
  }
  public function get_error(){
    return $this->error;
  }
}
?>
