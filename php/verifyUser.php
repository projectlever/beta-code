<?php
	session_start();
        if ( isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] == true ){
            header("Location: http://projectlever.com/profile.php");
            exit;
        }
	$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");	
	if (mysqli_connect_errno($con))	
		echo "Failed to connect to MySQL: " . mysqli_connect_error($con);	

	$sql = "SELECT * 
			FROM Users 
			WHERE Email = '".$_POST['email']."'";	
	if(!$result = mysqli_query($con, $sql))
		echo mysqli_error($con);  
	else{	
		if(mysqli_num_rows($result) == 0){
			echo "new user";
				/*<script>
					function register(message){
						alert(message);
					}
					register('You haven't registered yet');
					window.location = '/webfiles/login/register';
				</script>";*/
		}
		else{
			while ($row = mysqli_fetch_array($result)){
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
				echo "registered";
				exit();		
			}
		}
	}	
?>
