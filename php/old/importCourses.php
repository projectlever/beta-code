<?php

    /* Same exact file as import.php just for courses! */

    header('Content-Type:text/html; charset:UTF-8');
	
	$file             = "./University_of_Notre_Dame/NotreDameCourses_Oct8_2013.json";
	$school           = "College of Arts and Letters";
	//$department       = "Political Science";
	$university       = "University_of_Notre_Dame";
	$description      = "";
	$name             = "";
	$exceptions       = array(); // An array of names to skip
	
	/*$con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
	if (mysqli_connect_errno($con)){
		echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
	}
	$result = mysqli_query($con,"SELECT `Name` FROM `Advisors` WHERE `University` = 'Columbia_University'");
	while ($row = mysqli_fetch_array($result)){
	    echo "<script type='text/javascript'>document.write('".$row["Name"]."');</script>";
	}
	mysqli_close($con);
	exit;*/
	
	// Grab the json array from the $file variable, if that file exists
	if ( file_exists($file) === true ){
	    $courses = json_decode(file_get_contents($file),true);
	    $courses = $courses["data"];
	    // Open the SQL
	    $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
		if (mysqli_connect_errno($con)){
			echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
		}
		for ( $i = 0, $n = count($courses); $i < $n; $i++ ){
		    $course = $courses[$i];
		    // Grab the name(s) from the JSON array. If there is more than one name, echo the array of names and exit
    		// Check to see if the name is empty or FALSE
    		if ( empty($course["name"] ) === false ){
    		    $name = $course["name"][0];
        		// $name = utf8_decode(makeProperName($name)); This was for advisors
    		    if ( in_array($course["name"][0],$exceptions) === false ){
        		    $sqlAction = "INSERT";
        		    // Get the name and check to see if the name exists in the database
        		    $nameCheck = runSQL("SELECT `University` FROM `Course` WHERE `Name`='".mysqli_real_escape_string($con,trim($name))."'",$con);
        		    if ( mysqli_num_rows($nameCheck) > 0 ){
        		        // That advisor's name already exists in the database. Check to see if the university matches up
        		        while ( $row = mysqli_fetch_array($nameCheck) ){
        		            if ( $row["University"] == $university ){
        		                // There is a match so update all information
        		                $sqlAction = "UPDATE";
        		            }
        		        }
        		    }
        		    // Take the first element of all arrays containing the description.
    		        if ( $course["description"] != null ){
    		            $description = $course["description"][0];
    		        }

                    // Remove the title from the descriptio, then remove leading and trailing whitespace, remove any leading colon, and finally trim once more
                    $description = trim(preg_replace("/^:/","",trim(str_replace($name,"",$description))));
        		    if ( $sqlAction == "INSERT" ){
        		        // This is a brand new advisor so insert the information
        		        insertCourse($name,$university,$description,$school,$con);
        		    }
        		    else if ( $sqlAction === "UPDATE" ){
        		        updateCourse($name,$university,$school,$description,$con);
        		    }
    		    }
    		}
		}
		mysqli_close($con);
	}
	else {
	    echo $file . " does not exist.";exit;
	}
	echo $sqlAction . " COMPLETE --------------------------------------------------------------------------------------- ";
	
	function makeProperName($name){
	    if ( stripos($name,",") !== false ){
	        $splitName = explode(",",$name);
	        $name = preg_replace("/\s{2,}/"," ",trim($splitName[1]." ".$splitName[0]));
	    }
	    return $name;
	}
	function updateCourse($name,$university,$school,$description,$connection_var){
	    // Grab the advisor from the SQL
	    $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $school     = mysqli_real_escape_string($connection_var,$school);
        $description= mysqli_real_escape_string($connection_var,$description);
        
        $select = "SELECT * FROM `Course` WHERE `Name`='$name' AND `University`='$university'";
        $update = "UPDATE `Course` SET `School`='$school', `Description`='$description', `University`='$university' WHERE `Name`='$name' AND `University`='$university'";
        // The next line is for debugging purposes and is probably commmented out
        //echo $update;exit;
        $result = mysqli_query($connection_var,$update);
        if ( !$result ){
            echo mysqli_error($connection_var);exit;
        }
        else {
            echo "Update Successful: $name <br/>";
        }
	}
    function insertCourse($name,$university,$description,$school,$connection_var){
        $name        = mysqli_real_escape_string($connection_var,$name);
        $description = mysqli_real_escape_string($connection_var,$description);
        $school      = mysqli_real_escape_string($connection_var,$school);
        $university  = mysqli_real_escape_string($connection_var,$university);

        $insert  = "INSERT INTO `Course` (`Name`,`University`,`School`,`Description`) VALUES (";
        $insert .= "'$name','$university','$school','$description')";
        $result = mysqli_query($connection_var,$insert);
        if ( !$result ){
            echo mysqli_error($connection_var);
            echo "<br/>$name";exit;
        }
        else {
            echo "Successfully inserted: $name<br/>";
        }
        return;
    }
    function runSQL($query_string, $connection_var){
        $result = mysqli_query($connection_var,$query_string);
        if ( !$result ){
            echo mysqli_error($connection_var);exit;
        }
        else {
            return $result;
        }
    }
    /*
     * The cleanJSONArray function takes a json string representing an array of data for a professor. For example: $json may be  a json string
     * containing the departments that a professor is associated with. $string is the new information that we are looking to import into the database.
     * $string may be a department name, a school name, a link, or any other information we may be importing. The function decodes the json string into
     * an array and then compares all of the information in the array to the new information ($string). If it cannot find $string inside the array, then
     * it pushes $string into the array and re-encodes the json string.
     */
	function cleanJSONArray($json,$string){
        $array = json_decode($json,true);
        
        // HOPEFULLY this code doesn't have to fire
        if ( $array === false || $array == null || is_array($array) === false ){
            // $array is a string...duh
            if($array != $string){
            	$newarray = array($array,$string);
            	$array = $newarray;
            }
        }
        else if ( is_array($array) === true ){
            // If $department is a JSON object, loop through the entire array looking for a match between those strings
            // and the provided string
            $isEqual = false;
            for ( $i = 0, $n = count($array); $i < $n; $i++ ){
                if ( trim($array[$i]) == trim($string) ){
                    // If the link matches an element in the array, set the isEqual variable to true, which will tell
                    // the program not to add the link to the SQL.
                    $isEqual = true;
                    $i = $n;
                }
            }
            if ( $isEqual === false ){
                // If the link from $advisors was NOT matched with a link in the SQL, then add it to the SQL!
                array_push($array,trim($string));
            }
        }
        else {
            echo "Fail...Could not figure out what kind of string was given:<br/>";
            echo $array;exit;
        }
        
        $array = json_encode($array,JSON_FORCE_OBJECT);
        
        return $array;
    }
?>