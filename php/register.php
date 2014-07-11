<?
/*
 *    register.php
 *     @author: Ian Clark
 *	Registers a new user:
 *	Hashes password
 *	sets university session
 *	checks for multiple cases of an email
*/

    session_start();
    
    // Randomize user
    $univ = str_replace(" ","_",$_POST["university"]);
    if($univ == "Tufts_University")
    	$class = rand(0,4);
    else
    	$class = 4;
    
    if (isset($_POST['password']))
    	$_POST['password'] = md5($_POST['password']);
    
    if($_POST['type'])
        $type = "prof";
    else
        $type = "stud";
    
    $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_users");
    	
    if (mysqli_connect_errno($con))
    	echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
    
    
    $sql = "SELECT * 
    FROM Users WHERE Email = '$_POST[email]'";
    	
    $result = mysqli_query($con, $sql);
    	
    if(mysqli_fetch_array($result)){
    	if (!isset($_POST["ajax"])){
    	echo "
    		<script type='text/javascript'>
                function register(message){
    				alert(message);
    			}
    			register('You\'ve already registered');
    			window.location = '/webfiles/login/login';
    		</script>";
    	}
    	else {
    		echo "registered";
    	}
    }	
    elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
    	if (!isset($_POST['ajax'])){	
    	echo "
    		<script>
    			function register(message){
    				alert(message);
    			}
    			register('Not a valid email address');
    			window.location = '/webfiles/login/register';
    		</script>";
    	}
    	else {
    		echo "invalid email";
    	}
    }
    elseif($univ=="No School"){
    	if (!isset($_POST["ajax"])){
    		header("Location: http://projectlever.com/webfiles/login/register/school.html");
    	}
    	else {
    		echo "no school";
    	}
    }
    else{
    	$time = explode(" ",microtime());
    	if (isset($_POST['password'])){
    		$sql = "INSERT INTO Users (Email, Password, University, Name, School, Department, Focus, Log_In_Date, Type, Class) 
    		VALUES ('$_POST[email]', '".$_POST['password']."', '$_POST[university]', '', 'empty', 'empty', '','".$time[1]."', '$type', ".$class.")";
    	}
    	else {
    		$strength = 8;
    		$length = 9;
    		$vowels = 'aeuy';
    		$consonants = 'bdghjmnpqrstvz';
    		if ($strength & 1) {
    			$consonants .= 'BDGHJLMNPQRSTVWXZ';
    		}
    		if ($strength & 2) {
    			$vowels .= "AEUY";
    		}
    		if ($strength & 4) {
    			$consonants .= '23456789';
    		}
    		if ($strength & 8) {
    			$consonants .= '@#$%';
    		}
    		$password = '';
    		$alt = time() % 2;
    		for ($i = 0; $i < $length; $i++) {
    			if ($alt == 1) {
    				$password .= $consonants[(rand() % strlen($consonants))];
    				$alt = 0;
    			} else {
    				$password .= $vowels[(rand() % strlen($vowels))];
    				$alt = 1;
    			}
    		}
    		$password = md5($password);
    			$sql = "INSERT INTO Users (Email, `Password`, University, Name, School, Department, Focus, Log_In_Date, Type, Class) 
    			VALUES ('$_POST[email]', '$password', '".$univ."', '', '', '', '','".$time[1]."', '$type', ".$class.")";
    	}
    	if (!mysqli_query($con,$sql))
    		die('Error: ' . mysqli_error($con));
    		
    	$_SESSION['university'] = str_replace(" ","_",$univ);
    	$_SESSION['loggedin'] = true;
    	$_SESSION['email'] = $_POST['email'];
    	$_SESSION['school'] = "My College";
    	$_SESSION['department'] = "My Department";
    	$_SESSION['class'] = $class;
    		
    	$_SESSION['log_in_time'] = $time[1];
    	if (isset($_POST["ajax"])){
    		echo "complete";
    	}
    	else if($class == 0){
    		header("Location: http://projectlever.com/connectLater.html");
    	}
    	else {
    		header("Location: http://projectlever.com/edit_profile.php");
    	}
    		
    	$sql = "SELECT * FROM `Users` where `Email` = '" . $_POST['email'] . "'";
    	$result = mysqli_query($con,$sql);
    	if (!$result){
    		die('Error: ' . mysqli_error($con));
    		exit();
    	}
    	while ($row = mysqli_fetch_array($result)){
    		$_SESSION['user_id'] = $row['User_ID'];
    	}
}	
    mysqli_close($con);
?>
