<?php
/*
 * login.php
 * @author Ian Clark
 * Logs the user in
 * Checks to see if registered
 * Checks to see if passwords match
 */
    session_start();
	if (isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == true){
             header("Location: http://projectlever.com/profile.php");
             exit;
    }
    if (!isset($_POST["fb_login"]))
		$password = md5($_POST['password']);
	$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");

	if(mysqli_connect_errno($con))
		echo "Error: Failed to connect to MySQL: " . mysqli_connect_error($con);
	
	$sql = "SELECT * 
			FROM `Users` 
			WHERE `Email` = '".$_POST["email"]."'";
	$result = mysqli_query($con, $sql);
	if($result === false||is_bool($result)===true){
		echo mysqli_error($con);
        }
	else{
		if(mysqli_num_rows($result) == 0){
			echo "
				<script>
					function register(message){
						alert(message);
					}
					register('You haven\'t registered yet');
					window.location = '/webfiles/login/register';
				</script>";
		}
		else{
			while ($row = mysqli_fetch_array($result)){
				if(isset($password) && $row['Password'] != $password){
					echo "
						<script>
							var Error;
							function error(message){
								alert(message);
							}
							error('Incorrect password');
							window.location = '/webfiles/login/login';
						</script>";
				}
				elseif(isset($_POST["fb_login"])||$row['Password'] == $password){
					$_SESSION['user_id'] = $row['User_ID'];
					$_SESSION['university'] = $row['University'];
					$_SESSION['loggedin'] = true;
					$_SESSION['email'] = $row['Email'];
					$_SESSION['school'] = $row['School'];
					$_SESSION['department'] = $row['Department'];
					$_SESSION['class'] = $row['Class'];
					$time = explode(" ",microtime());
					$_SESSION['log_in_date'] = $time[1];
					// Update SQL so that the log in time is stored there for future use
					$sql = "UPDATE Users SET `Log_In_Date`= '".$time[1]."' where `Email` = '".$row['Email']."'";
					$result = mysqli_query($con,$sql);
					if (!$result){
						die('Error: ' . mysqli_error($con));
					}
					if (isset($_POST["fb_login"])){
						echo "login successful";
					}
					else {
						if (isset($_POST["landingPage"]) && $_SESSION['class'] != 0){
							echo "login successful";
                                                        exit;
						}
						else if (isset($_POST["landingPage"]) && $_SESSION['class'] == 0){
							echo "closed";
                                                        exit;
						}
						else if($_SESSION['class'] == 0){
							header("Location: http://www.projectlever.com/connectLater.html");
                                                        exit;
						}
						else {
							header("Location: http://www.projectlever.com/profile.php");
                                                        exit;
						}
					}
				}   
			}
		}
	}         
    mysqli_close($con);
?>
