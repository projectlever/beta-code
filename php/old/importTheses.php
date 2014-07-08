<?php

    /* Same exact file as importGrants.php just for theses! */

    header('Content-Type:text/html; charset:UTF-8');
	
	$file             = "./Columbia_University/ColumbiaTheses_Oct9_2013.json";
	$school           = "Columbia College"; // The school may be different than Columbia College. This is checked by looking for a different school
	// name in parenthesis in the department column
	$university       = "Columbia_University";
	$abstract      = "";
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
	    $theses = json_decode(file_get_contents($file),true);
	    $theses = $theses["data"];
	    // Open the SQL
	    $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
		if (mysqli_connect_errno($con)){
			echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
		}
		for ( $i = 0, $n = count($theses); $i < $n; $i++ ){
		    $school = "Columbia College";
		    $thesis = $theses[$i];
		    // Grab the name(s) from the JSON array. If there is more than one name, echo the array of names and exit
    		// Check to see if the name is empty or FALSE
    		if ( $thesis["type"][0] == "Undergraduate theses" ){
        		if ( empty($thesis["author"] ) === false ){
        		    $name = makeProperName($thesis["author"][0]);
            		// $name = utf8_decode(makeProperName($name)); This was for advisors
        		    if ( in_array($thesis["author"][0],$exceptions) === false ){
            		    $sqlAction = "INSERT";
            		    // Get the name and check to see if the name exists in the database
            		    $nameCheck = runSQL("SELECT `University` FROM `Thesis` WHERE `StudentName`='".mysqli_real_escape_string($con,trim($name))."'",$con);
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
        		        if ( $thesis["abstract"] != null ){
        		            $abstract = implode("<br/><br/>",$thesis["abstract"]);
        		        }
        		        if ( $thesis["advisor"] != null ){
        		            $advisor = makeProperName($thesis["advisor"][0]);
        		        }
        		        if ( $thesis["department"] != null ){
        		            $department = $thesis["department"][0];
        		            // Look for parenthesis
        		            if ( stripos($department,"(") !== false ){
        		                $school = substr($department,stripos($department,"(")+1);
        		                $school = trim(substr($school,0,stripos($school,")")));
        		                $department = trim(str_replace("(".$school.")","",$department));
        		            }
        		        }
        		        if ( $thesis["name"] != null ){
        		            $title = $thesis["name"][0];
        		        }
            		    if ( $sqlAction == "INSERT" ){
            		        // This is a brand new advisor so insert the information
            		        insertThesis($name,$university,$school,$department,$title,$abstract,$advisor,$con);
            		    }
            		    else if ( $sqlAction === "UPDATE" ){
            		        updateThesis($name,$university,$school,$department,$title,$abstract,$advisor,$con);
            		    }
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
	function updateThesis($name,$university,$school,$department,$title,$abstract,$advisor,$connection_var){
	    // Grab the advisor from the SQL
	    $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $school     = mysqli_real_escape_string($connection_var,$school);
        $department     = mysqli_real_escape_string($connection_var,$department);
        $title     = mysqli_real_escape_string($connection_var,$title);
        $abstract     = mysqli_real_escape_string($connection_var,$abstract);
        $advisor     = mysqli_real_escape_string($connection_var,$advisor);
        
        $select = "SELECT * FROM `Thesis` WHERE `StudentName`='$name' AND `University`='$university'";
        $update = "UPDATE `Thesis` SET `School`='$school',`Department`='$department',`Name`='$title',`Advisor1`='$advisor',`Abstract`='$abstract', `University`='$university' WHERE `StudentName`='$name' AND `University`='$university'";
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
    function insertThesis($name,$university,$school,$department,$title,$abstract,$advisor,$connection_var){
        $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $school     = mysqli_real_escape_string($connection_var,$school);
        $department     = mysqli_real_escape_string($connection_var,$department);
        $title     = mysqli_real_escape_string($connection_var,$title);
        $abstract     = mysqli_real_escape_string($connection_var,$abstract);
        $advisor     = mysqli_real_escape_string($connection_var,$advisor);

        $insert  = "INSERT INTO `Thesis` (`StudentName`,`University`,`School`,`Department`,`Name`,`Abstract`,`Advisor1`) VALUES (";
        $insert .= "'$name','$university','$school','$department','$title','$abstract','$advisor')";
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