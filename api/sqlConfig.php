<?php 
$config = include("config.php");
define("SQL_DATABASE_REQUIRED","sql_connect requires \$database to be defined");

$function_error = "";
function sql_connect($database){
  // Connect to an sql database
  global $function_error;
  if ( !$database ){
    $function_error = SQL_DATABASE_REQUIRED;
    return FALSE;
  }

$con = mysqli_connect("localhost","'" . $config["DB_USER"] . "'","'" . $config["DB_PASSWORD"] . "'","'" . $config["DB_NAME"] . "'"); 

  if (mysqli_connect_errno($con)){
    $function_error = mysqli_error($con);
    return FALSE;
  }
  return $con;
}
function sql_query($con,$sql){
  // Nicety function that runs an sql query and takes care of error handling
  global $function_error;
  $result = mysqli_query($con,$sql);
  if ( !$result )
    die(mysqli_error($con));
  else
    return $result;
}
function sql_escape($con,$value){
  // Shorter function for calling mysqli_real_escape_string
  return mysqli_real_escape_string($con,$value);
}
?>
