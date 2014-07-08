<?php

    /**
     * This file contains all generalized functions that will be used in importing information from import.io to our database.
     * Created: 10-23-2013 
     */
     
    // Constants
    $TYPE_ERROR = "1: Please select the type of thing to import: 'Advisors', 'Courses', 'Theses', 'All'.";
    
    // Default variables
    $defaultDatabase        = "svetlana_Total";
    $multipleNameWarning    = array();
    
    if ( isset($_GET["start"]) === true ){
	    $start              = $_GET["start"];
	    $advisorCounter     = $start;
	    $edited             = $start;
	}
	else {
	    $start              = -1;
	    $advisorCounter     = 0;
	    $edited             = 0;
	}
    
    // Connect to the provided database
    function sqlConnect($database){
        global $defaultDatabase; // Connect to the default database if $database is not provided
        
        if ( $database == null ){
            $database = $defaultDatabase;
        }
        
        $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6",$database);
        if (mysqli_connect_errno($con)){
        	echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
        	exit;
        }
        return $con;
    }
    // Debug functions
    function debug($type){
        global $counter, $name, $block, $header, $info, $school;
        preg_match("/\S{1,}/",$type,$letters);
        if ( $type != "" && empty($letters) === false ){
            $thingsToCheck = explode(",",$type);
            for ( $i = 0, $n = count($thingsToCheck); $i < $n; $i++ ){
                $type = $thingsToCheck[$i];
                if ( $type == "names"){
                    echo "$counter) $name<br/>";
                }
                else if ( $type == "info" ){
                    if ( $_GET["is_empty"] == 1 ){
                    	if ( ctype_space(strip_tags($info)) == true ){
                    		echo $counter.") ".$info . "<br/>";
                    	}
                    }
                    else {
                    	echo "$counter) $info<br/><br/>";
                    }
                }
                else if ( $type == "headers" ){
                    if ( $_GET["is_empty"] == 1 ){
                    	if ( ctype_space(strip_tags($header)) == true ){
                    		echo $counter.") ".$header . "<br/>";
                    	}
                    }
                    else {
                    	echo "$counter) $header<br/><br/>";
                    }
                }
                else if ( $type == "blocks" ){
                    if ( $_GET["is_empty"] == 1 ){
                    	if ( ctype_space(strip_tags($block)) == true ){
                    		echo $counter.") ".$block . "<br/>";
                    	}
                    }
                    else {
                    	echo "$counter) $block<br/><br/>";
                    }
                }
                else if ( $type == "schools" ){
                    echo "$counter) $school<br/>";
                }
                $counter++;
            }
            return true;
        }
        return false;
    }
    
    
    // Helper SQL functions
    function updateAdvisor($connection_var){
        global $name, $university, $department, $school, $picture, $block, $info, $header, $rawSchool, $rawDepartment, $rawUniversity, $edited;
        
	    // Grab the advisor from the SQL
	    $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $block      = mysqli_real_escape_string($connection_var,$block);
        $info       = mysqli_real_escape_string($connection_var,$info);
        $header     = mysqli_real_escape_string($connection_var,$header);
        
        // For the sake of searching, remove the middle intial and all letters followed by a period. If we find a match with this search (REGEXP) 
        // then we will update the name to contain the middle initial
        $regexp = "[ a-zA-Z\\.\\-]{0,}";
        $newName = preg_replace("/\s{2,}/"," ",preg_replace("/[a-zA-Z]{1,}\.\s*/","",$name));
        $names = explode(" ", $newName);
        
        $newName = "[[:<:]]";
        for ($i = 0, $n = count($names); $i < $n; $i++ ){
            if ($names[$i] != ""){
                $newName .= $names[$i].$regexp;
            }
        }
        $newName .= "[[:>:]]";
        
        $select = "SELECT * FROM `Advisor` WHERE `Name` REGEXP '".mysqli_real_escape_string($connection_var,$newName)."' AND `University`='$university'";
        $result = mysqli_query($connection_var,$select);
        if ( !$result ){
            echo mysqli_error($connection_var);exit;
        }
        else {
            while ($row = mysqli_fetch_array($result)){
                $jsonDepartment = verifyJSONArray(cleanJSONArray($row["Department"],$department,$connection_var));
                $jsonSchool     = verifyJSONArray(cleanJSONArray($row["School"],$school,$connection_var));
                $oldName = $row["Name"];
            }
            
            if ( $jsonDepartment == "" || $jsonSchool == "" ){
                echo "ERROR! Empty JSON objects! Department:<br/>".$row["Department"]."<br/> School:".$row["School"]."<br/>";
                echo "Occurred while inserting/updating $name";exit;
            }
            
            $reset = $_GET["reset"];
            $thingsToReset = explode(",",$reset);
            
            for ( $i = 0, $n = count($thingsToReset); $i < $n; $i++ ){
                if ( $thingsToReset[$i] != "" ){
                    switch ($thingsToReset[$i]){
                        case "schools":
                            $school = $rawSchool;
                            break;
                    }
                }
            }
            
            $jsonDepartment = removeEmptyFields(mysqli_real_escape_string($connection_var,$jsonDepartment));
            $jsonSchool     = removeEmptyFields(mysqli_real_escape_string($connection_var,$jsonSchool));

            $update = "UPDATE `Advisor` SET `School`='$jsonSchool', `Department`='$jsonDepartment', `Header`='$header',";
            
            if ( strlen($name) > strlen(mysqli_real_escape_string($connection_var,$oldName)) ){
                $update .= " `Name` = '$name',";
            }
            
            if ( stripos($_GET["skip"],"info") !== false ){
                $update .= "`Info`='',";
            }
            else {
                $update .= "`Info`='$info',";
            }
            
            $update .= "`Block`='$block', `University`='$university', `Scraped_Level`='Secondary', `Picture`='$picture' WHERE `Name` REGEXP '".mysqli_real_escape_string($connection_var,$newName)."' AND `University`='$university'";
            // The next line is for debugging purposes and is probably commmented out
            //echo $update;exit;
            $result = mysqli_query($connection_var,$update);
            if ( !$result ){
                echo mysqli_error($connection_var);exit;
            }
            else {
                $edited += 1;
                echo "$edited) Update Successful: $name <br/>";
                echo $jsonDepartment . "<br/>";
                echo $jsonSchool . "<br/><br/>";
                checkForDoubleImport($connection_var);
            }
            return true;
        }
	}
    function insertAdvisor($connection_var){
        global $name, $university, $school, $department, $picture, $block, $info, $header, $edited;
        
        $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $block      = mysqli_real_escape_string($connection_var,$block);
        $info       = mysqli_real_escape_string($connection_var,$info);
        $header     = mysqli_real_escape_string($connection_var,$header);
        
        $insert  = "INSERT INTO `Advisor` (`Name`,`University`,`School`,`Department`,`Picture`,`Block`,`Info`,`Header`,`Scraped_Level`) ";
        $insert  .= "VALUES ('$name','$university','$school','$department','$picture','$block','$info','$header','Secondary')";
        
        $result = mysqli_query($connection_var,$insert);
        if ( !$result ){
            echo mysqli_error($connection_var);
            echo "<br/>$name";exit;
        }
        else {
            $edited += 1;
            echo "$edited) Successfully inserted: $name<br/>";
        }
        return true;
    }
    function updateCourse($connection_var){
        global $name, $university, $school, $description;
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
            checkForDoubleImport($connection_var);
        }
        return true;
	}
    function insertCourse($connection_var){
        global $name, $university, $description, $school;
        
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
        return true;
    }
    function updateThesis($connection_var){
        global $name, $university, $school, $department, $title, $abstract, $advisor;
        
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
        return true;
	}
    function insertThesis($connection_var){
        global $name, $university, $school, $department, $title, $abstract, $advisor;
        
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
        return true;
    }
    // Takes a connection variable (from mysqli_connect()) and will execute the SQL query string
    function runSQL($connection_var,$query_string){
        $result = mysqli_query($connection_var,$query_string);
        if ( !$result ){
            echo "ERROR! ".mysqli_error($connection_var);exit;
        }
        else {
            return $result;
        }
    }
    // This function checks to see if the thing (advisor, course, thesis, etc...) has already been imported. If it has, we are going to UPDATE
    // rather than INSERT the thing's information into the database
    function checkForPreviousImport(){
        global $name, $blockToCheck, $con, $runType, $sqlAction, $university, $previousHeader, $school, $department, $previousInfo;
        // Remove all middle names from the name. This means all words or letters than end in a period
        $temp_name = preg_replace("/[a-zA-Z]{1,}\.\s*/","",$name);
        // Create a REGEXP out of the name
        $names = explode(" ",$temp_name);
        $regexp = "[ a-zA-Z\\-\\.]{0,}";
        $temp_name = "[[:<:]]";
        for ( $i = 0, $n = count($names); $i < $n; $i++ ){
            if ( $names[$i] != "" ){
                $temp_name .= $names[$i] . $regexp; 
            }
        }
        $temp_name .= "[ a-zA-Z,\\.]{0,}[[:>:]]";
	    // Get the name and check to see if the name exists in the database
	    $nameCheck = runSQL($con,"SELECT `University`,`School`,`Department`,`Name` FROM `$runType` WHERE `Name` REGEXP '".mysqli_real_escape_string($con,trim($temp_name))."'");
	    if ( mysqli_num_rows($nameCheck) > 0 ){
	        // That advisor's name already exists in the database. Check to see if the university matches up
	        while ( $row = mysqli_fetch_array($nameCheck) ){
	            if ( $row["University"] == $university && stripos($row["School"],$school) !== FALSE && stripos($row["Department"],$department) !== FALSE ){
	                // There is a match so update all information
	                $blockToCheck   = $row["Block"];
	                $previousPic    = $row["Picture"];
	                $previousHeader = $row["Header"];
	                $previousInfo   = $row["Info"];
	                if ( !$_GET["check"] ){
	                    echo "BEFORE UPDATE:<br/>School: ".$row["School"]."<br/>Depart: ".$row["Department"]."<br/><br/>";
	                }
	                $sqlAction = "UPDATE";
	                return;
	            }
	        }
	    }
	    else {
	        // If the REGEXP failed to match, then try a normal name match
	        $nameCheck = runSQL($con,"SELECT `University` FROM `$runType` WHERE `Name` = '".mysqli_real_escape_string($con,trim($thing_name))."'");
	        if ( mysqli_num_rows($nameCheck) > 0 ){
	            if ( isset($_GET["debug"]) && $_GET["debug"] == true ){
        	        echo "WARNING: Match was found using the non-regex name '$name'. Attempted regex match using '$temp_name'. Please verify that ";
        	        echo "this is the correct match and that there are not multiple results under this name. <br/>";
	            }
    	        // That advisor's name already exists in the database. Check to see if the university matches up
    	        while ( $row = mysqli_fetch_array($nameCheck) ){
    	            if ( $row["University"] == $university ){
    	                // There is a match so update all information
    	                $blockToCheck   = $row["Block"];
    	                $sqlAction = "UPDATE";
    	                return;
    	            }
    	        }
    	    }   
    	    else {
    	        if ( isset( $_GET["debug"] ) && $_GET["debug"] == true ){
    	            echo "Warning! No match for name search using '$name' and '$temp_name'. Inserting...<br/>";
    	        }
    	    }
	    }
	    $sqlAction = "INSERT";
	    return;
    }
    /*
     * The cleanJSONArray function takes a json string representing an array of data for a professor. For example: $json may be  a json string
     * containing the departments that a professor is associated with. $string is the new information that we are looking to import into the database.
     * $string may be a department name, a school name, a link, or any other information we may be importing. The function decodes the json string into
     * an array and then compares all of the information in the array to the new information ($string). If it cannot find $string inside the array, then
     * it pushes $string into the array and re-encodes the json string.
     */
	function cleanJSONArray($json,$string,$con){
	    global $name;
        $array = json_decode($json,true);

        if ( is_array($array) === true ){
            // If $department is a JSON object, loop through the entire array looking for a match between those strings
            // and the provided string
            if ( is_array($string) === true ){
                print_r($string);
                exit;
            }
            for ( $i = 0, $n = count($array); $i < $n; $i++ ){
                if ( is_array($array[$i]) === true ){
                    print_r($array[$i]);exit;
                }
                if ( trim(preg_replace("/[\"\'\\\]/","",$array[$i])) == trim($string) ){
                    return $json;
                }
            }
            $array[] = trim(preg_replace("/[\"]{1,}/","",$string));
            return json_encode($array,JSON_FORCE_OBJECT);
        }
        else {
            if ( is_string($array) === true ){
                if ( trim(preg_replace("/[\'\"\\\]/","",$array)) != trim($string) ){
                    return json_encode(array($array, preg_replace("/[\"]{1,}/","",$string)), JSON_FORCE_OBJECT);
                }
                else {
                    return json_encode(array($array),JSON_FORCE_OBJECT);
                }
            }
            else if ( $array === null ){
                return json_encode(array(preg_replace("/[\"]{1,}/","",$string)),JSON_FORCE_OBJECT);
            }
            else {
                echo "Fail...Could not figure out what kind of object $json is...Return value:<br/>";
                echo $array . "<br/>";
                echo $name;exit;
            }
        }
    }
    // This function checks for any errors in the JSON object. It is a continually growing function
    function verifyJSONArray($json){
        global $name;
        preg_match("/\\\/",$json,$matches);
        if ( count($matches) > 0 ){
            $json = preg_replace("/[\\\]/","",$json);
            preg_match("/\"\"/",$json,$matches);
            if ( count($matches) > 0 ){
                echo "ERROR!<br/>";
                echo $json . "<br/>Occurred while inserting/updating $name";exit;
            }
        }
        else {
            if ( stripos($json,":{\"") !== false ){
                preg_match_all("/[a-zA-Z\s]{1,}/",$json,$dept);
                $dept = mysqli_real_escape_string($con,json_encode($dept[0],JSON_FORCE_OBJECT));
                echo "Error! " . $json . "<br/>$dept";exit;
            }
        }
        return $json;
    }
    function removeEmptyFields($json){
        return preg_replace("/\"[0-9]{1,}\":\"\"/","",$json);
    }
    // This functions checks to see if the current advisor, course, thesis, etc. is in the database more than once. If it is, then the rows will be
    // concatenated into one. The conditions for being the same are: similar name (at least 80% correct for courses and theses, and first and last name
    // for advisors) and the same university.
    function checkForDoubleImport($con){
        global $name, $multipleNameWarning;
        
        $newName = preg_replace("/\s{2,}/"," ",preg_replace("/[a-zA-Z]{1,}\.\s*/","",$name));
        $names = explode(" ", $newName);
        $regexp = "[ a-zA-Z\\.\\-]{0,}";
        
        $newName = "[[:<:]]";
        for ($i = 0, $n = count($names); $i < $n; $i++ ){
            if ($names[$i] != ""){
                $newName .= $names[$i].$regexp;
            }
        }
        $newName .= "[[:>:]]";
        
        $sql = "SELECT * FROM `Advisor` WHERE `Name` REGEXP '".mysqli_real_escape_string($con,$newName)."'";
        $result = mysqli_query($con,$sql);
        if ( !$result ){
            echo mysqli_error($con);exit;
        }
        else {
            if ( mysqli_num_rows($result) > 1){
                $quer   = "SELECT * FROM `Advisor` WHERE";
                while ( $row = mysqli_fetch_array($result) ){
                    $quer .= " `Advisor_ID` = " . $row["Advisor_ID"] . " OR ";
                }
                $quer = substr($quer,0,strrpos($quer," OR"));
                $multipleNameWarning[] = $quer;
            }
        }
        return;
    }
    // Displays the data if a manual import is necessary
    function getData($connection_var){
        global $name, $university, $department, $school, $picture, $block, $info, $header;
	    // Grab the advisor from the SQL
	    $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $block      = mysqli_real_escape_string($connection_var,$block);
        $info       = mysqli_real_escape_string($connection_var,$info);
        $header     = mysqli_real_escape_string($connection_var,$header);
        
        return array($name,$university,$block,$info,$header,$university,$department,$school);
    }
    function getManualImports(){
        global $manualImports,$run;
        $manualData = array();
        
        $types = explode(",",$run);
	    
        for ($j = 0, $m = count($manualImports); $j < $m; $j++ ){
            for ( $i = 0, $n = count($types); $i < $n; $i++ ){
    	        if ( $types[$i] != "" ){
    	            $manualData[] = call_user_func("import".$types[$i],$manualImports[$j]);
    	        }
    	    }    
	    }
	    for ( $i = 0, $n = count($manualData); $i < $n; $i++ ){
	        print_r($manualData[$i]);
	        echo "<br/><hr/><br/>";
	    }
	    return;
    }
    function resetValues(){
        global $block, $name, $header, $blockToCheck, $picture, $previousPic, $previousHeader, $info;
        $block          = "";
        $name           = "";
        $header         = "";
        $blockToCheck   = "";
        $picture        = "";
        $previousPic    = "";
        $previousHeader = "";
        $info           = "";
        return;
    }
?>
