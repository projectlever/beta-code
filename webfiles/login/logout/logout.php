<?php
/*
 * logout.php
 * @author Ian Clark
 * Logs the user in
 * Checks to see if registered
 * Checks to see if passwords match
 */
 	session_start();
	session_destroy();
if ( isset($_GET["new"]) ):
  {header("Location: http://projectlever.com/beta-code2/home.php");}
else:
  {header("Location: http://projectlever.com/beta-code2/webfiles/login/login");}
exit();
?>
