<?php

    /**
     * This file contains all generalized functions that will be used in importing information from import.io to our database.
     * Created: 10-23-2013 
     */
     
    header('Content-Type:text/html; charset=UTF-8');
    // Include the name fixer object and import functions
    if ( class_exists("NameFixer") === false ){
        require("../../../lib/NameFixer.php");
    }
    if ( function_exists("setAdvisorNameUsingIndex") === false ){
        require("../../../lib/importRules.php");
    }
    
    // Constants
    $TYPE_ERROR = "1: Please select the type of thing to import: 'Advisors', 'Courses', 'Theses', 'All'.";
    
    // Default variables
    $defaultDatabase        = "svetlana_Total";
    $multipleNameWarning    = array();
    $similarNames           = array();
    $previousSchool         = array();
    $previousDepartment     = "";
    $newName                = "";
    $rawDepartment          = "";
    $previousCheckComplete  = false;
    $count                  = 0;
    $testNumber             = -1;
    $passedStartAdvisor     = true;
    $startAdvisor           = "";
    $fixer                  = new NameFixer();
    $runType                = $_GET["type"];
    $completeCounter        = 0;
    $blobCheckComplete      = false;
    
    if ( isset($_GET["start_advisor"]) && $_GET["start_advisor"] != "" ){
        $startAdvisor = $_GET["start_advisor"];
        $passedStartAdvisor = false;
    }
    
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
        global $defaultDatabase, $rawDepartment, $department, $con; // Connect to the default database if $database is not provided
        if ( $rawDepartment == "" ){
            $rawDepartment = $department;
        }
        
        if ( $database == null ){
            $database = $defaultDatabase;
        }
        
        $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6",$database);
        if (mysqli_connect_errno($con)){
        	echo "Failed to connect to MySQL: " . mysqli_connect_error($con);
        	exit;
        }
        if ( isset($_GET["test"]) ){
            $testNumber = $_GET["test"];
        }
        echo "<style type='text/css'>.change:hover{text-decoration:underline;cursor:pointer;}</style>";
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
        global $name, $university, $department, $school, $picture, $block, $info, $header, $edited, 
            $advisor, $previousDepartment, $previousSchool, $rawDepartment, $newName, $advisorCounter, $testNumber, $completeCounter, $blobCheckComplete;
        
        if ( $blobCheckComplete === false ){
            echo "Nothing was added to the blob! Make sure that addIntoBlob(\$smaller) is running properly.";exit;
        }
        $url = $advisor["_pageUrl"];
        // Log the changes made
        $logFile = "../logs/".$rawDepartment.".json";
        if ( file_exists($logFile) ){
            $logInfo = json_decode(file_get_contents($logFile),true);
            $updateBool = true;
            $logInfo[$name] = array(
                "Block"         => $block,
                "Info"          => $info,
                "Header"        => $header,
                "School"        => $school,
                "Department"    => $department,
                "University"    => $university,
                "Picture"       => $picture,
                "URL"           => $url
            );
        }    
        else {
            $logInfo = array("$name"=>array(
                "Block"         => $block,
                "Info"          => $info,
                "Header"        => $header,
                "School"        => $school,
                "Department"    => $department,
                "University"    => $university,
                "Picture"       => $picture,
                "URL"           => $url
            ));
            $updateBool = true;
            file_put_contents($logFile,json_encode($logInfo,JSON_FORCE_OBJECT));
        }
        if ( $updateBool == true ){
            // Backup all information in the database for this advisor.
            
            $backup = runSQL($connection_var,"SELECT * FROM `Advisor` WHERE `Name` = '" . mysqli_real_escape_string($connection_var,$name) . "'");
            if ( !$backup ){
                echo mysqli_error($connection_var);exit;
            }
            else {
                if ( mysqli_num_rows($backup) > 1 ){
                    echo "ERROR! Multiple advisors with same name!<br/>";
                    while ( $row = mysqli_fetch_array($backup) ){
                        echo "`Advisor_ID` = " . $row["Advisor_ID"] . " OR ";
                    }
                    exit;
                }
                else {
                    $backupInfo = array();
                    while ( $row = mysqli_fetch_array($backup) ){
                        $backupInfo["Block"] = $row["Block"];
                        $backupInfo["Info"] = $row["Info"];
                        $backupInfo["Header"] = $row["Header"];
                        $backupInfo["School"] = $row["School"];
                        $backupInfo["Department"] = $row["Department"];
                        $backupInfo["University"] = $row["University"];
                        $backupInfo["Link"] = $row["Link"];
                        $backupInfo["Picture"] = $row["Picture"];
                        $backupInfo["Blob"] = $row["Blob"];
                        $backupInfo["Processed_Text"] = $row["Processed_Text"];
                        $backupInfo["Scraped_Level"] = $row["Scraped_Level"];
                        $backupInfo["Class"] = $row["Class"];
                        $backupInfo["Division"] = $row["Division"];
                        $backupInfo["Tags"] = $row["Tags"];
                        $backupInfo["Email"] = $row["Email"];
                    }
                }
            }
            $backUpFile = "../backup/".$rawDepartment.".json";
            if ( file_exists($backUpFile) ){
                $fileInfo = json_decode(file_get_contents($backUpFile),true);
                $fileInfo[$name] = $backupInfo;
                file_put_contents($backUpFile,json_encode($fileInfo,JSON_FORCE_OBJECT));
                echo "Backup file updated: " . $backUpFile . "<br/>";
            }    
            else {
                file_put_contents($backUpFile,json_encode(array("$name"=>$backupInfo),JSON_FORCE_OBJECT));
                echo "Backup file written: " . $backUpFile . "<br/>";
            }
            // Update the SQL
            if ( $previousDepartment == "Array" ){
                $previousDepartment = "";
            }
            if ( $previousSchool == "Array" ){
                $previousSchool = "";
            }
            if ( $previousDepartment != "" ){
                $updateDepartment = compareInclude($previousDepartment,$department);
            }
            if ( $previousSchool != "" ){
                $updateSchool = compareInclude($previousSchool,$school);
            }
            // Check to see if it's a json array
            if ( stripos($updateDepartment,"{") === false ){
                $updateDepartment = json_encode(array($department),JSON_FORCE_OBJECT);
            }
            if ( stripos($updateSchool,"{") === false ){
                $updateSchool = json_encode(array($school),JSON_FORCE_OBJECT);
            }
            if ( $newName != "" ){
                $check = runSQL($connection_var,"SELECT * FROM `Advisor` WHERE `Name`='".safeString($connection_var,$newName)."'");
                if ( !$check ){
                    echo mysqli_error($connection_var);
                }
                else {
                    if ( mysqli_num_rows($check) > 0 ){
                        $name = $newName;
                    }
                    else {
                        $uName = ",`Name`='".safeString($connection_var,$newName)."'";      
                    }
                }
            }
            else {
                $uName = "";
            }
            // Remove picture from block
            // Find all of the links in $block, $header, $info, and $picture
            $block = rel_to_abs_path_2($block,$url);
            $picture = rel_to_abs_path_2($picture,$url);
            $info = rel_to_abs_path_2($info,$url);
            $header = rel_to_abs_path_2($header,$url);

            //echo $info;exit;
            //echo $block;exit;
            
            $update = runSQL($connection_var,"UPDATE `Advisor` SET `Block`='".safeString($connection_var,$block)."',`Info`='".safeString($connection_var,$info)."',
                `Header`='".safeString($connection_var,$header)."', `School`='".safeString($connection_var,$updateSchool)."', `Department`='".safeString($connection_var,$updateDepartment)."',
                `Link`='".safeString($connection_var,$url)."', `Picture`='".safeString($connection_var,$picture)."' $uName WHERE `Name` = '".safeString($connection_var,$name)."'");

            if ( !$update ){
                echo mysqli_error($connection_var);exit;
            }
            else {
                file_put_contents($logFile,json_encode($logInfo,JSON_FORCE_OBJECT));
                $completeCounter++;
                if ( $newName != "" ){
                    echo "$completeCounter) Updated $newName<br/>";
                }
                else {
                    echo "$completeCounter) Updated $name<br/>";
                }
                echo $updateSchool . "<br/>";
            }
            if ( $advisorCounter == $testNumber ){
                echo "Test complete<br/>";
                exit;
            }
        }
        echo "<script>window.parent.completed('".$name."');</script>";
        resetValues();
        $newName = "";
	}
	function safeString($con,$str){
	    return mysqli_real_escape_string($con,$str);
	}
    function insertAdvisor($connection_var){
        global $name, $university, $school, $department, $picture, $block, $info, $header;
        
        $name       = mysqli_real_escape_string($connection_var,$name);
        $university = mysqli_real_escape_string($connection_var,$university);
        $block      = mysqli_real_escape_string($connection_var,$block);
        $info       = mysqli_real_escape_string($connection_var,$info);
        $header     = mysqli_real_escape_string($connection_var,$header);

        $importSchool     = json_encode(array($school)    ,JSON_FORCE_OBJECT);
        $importDepartment = json_encode(array($department),JSON_FORCE_OBJECT);
        
        $insert  = "INSERT INTO `Advisor` (`Name`,`University`,`School`,`Department`,`Picture`,`Block`,`Info`,`Header`,`Scraped_Level`) ";
        $insert  .= "VALUES ('$name','$university','$importSchool','$importDepartment','$picture','$block','$info','$header','Secondary')";
        
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
    // Takes a connection variable (from mysqli_connect()) and will execute the SQL query string
    function runSQL($connection_var,$query_string){
        $result = mysqli_query($connection_var,$query_string);
        if ( !$result ){
            echo "ERROR! ".mysqli_error($connection_var)."<br/>";
            echo $query_string;exit;
        }
        else {
            return $result;
        }
    }
    // This function checks to see if the thing (advisor, course, thesis, etc...) has already been imported. If it has, we are going to UPDATE
    // rather than INSERT the thing's information into the database
    function checkForPreviousImport(){
        global $name, $blockToCheck, $con, $runType, $sqlAction, $university, $previousCheckComplete, $passedStartAdvisor, $startAdvisor,
            $previousHeader, $school, $department, $previousInfo, $similarNames, $previousPic, $previousSchool, $previousDepartment, $newName;
        
        $newName = "";
        if ( function_exists("parseLink") === false ){
            require("../../../lib/linkParser.php");
        }
	    // Get the name and check to see if the name exists in the database
	    
	    if ( isset($_GET["completed"]) ){
	        $completed = json_decode($_GET["completed"],true);
	    }
	    $nameCheck = runSQL($con,"SELECT `Header`,`Block`,`School`,`Department`,`Info`,`Name` FROM `$runType` WHERE `University` = '$university'");
	    if ( mysqli_num_rows($nameCheck) > 0 ){
	        // Check to see if the advisor already exists in the database
	        $id = 1;
	        while ( $row = mysqli_fetch_array($nameCheck) ){
	            $lev = levenshtein($row["Name"],$name);
	            if ( $name == $row["Name"] ){
	                $sqlAction      = "UPDATE";
	                $blockToCheck   = $row["Block"];
	                $previousHeader = $row["Header"];
	                $previousInfo   = $row["Info"];
	                $previousPic    = $row["Picture"];
	                $previousSchool = $row["School"];
	                $previousDepartment = $row["Department"];
	                echo $name . "<br/>";
	                //print_r($previousDepartment);
	                echo "<br/>";
	                return "UPDATE";
	            }
	            else if ( ( $lev > -1 && $lev < 4 ) ){
	                $nameInfo = json_decode(file_get_contents("http://www.projectlever.com/import/lib/names.json"),true);
	                if ( $nameInfo[$name] != null ){
	                    if ( stripos($nameInfo[$name],"-continue") === false ){
	                        $newName = str_replace("-continue","",$nameInfo[$name]);
	                        //echo $newName;exit;
	                    }
	                    $sqlAction      = "UPDATE";
    	                $blockToCheck   = $row["Block"];
    	                $previousHeader = $row["Header"];
    	                $previousInfo   = $row["Info"];
    	                $previousPic    = $row["Picture"];
    	                $previousSchool = $row["School"];
    	                $previousDepartment = $row["Department"];
    	                echo $name . "<br/>";
    	                //print_r($previousDepartment);
    	                return "UPDATE";
	                }
	                else if ( $nameInfo[$row["Name"]] != null ){
	                    if ( stripos($nameInfo[$row["Name"]],"-continue") === false ){
	                        $newName = str_replace("-continue","",$nameInfo[$row["Name"]]);
	                    }
	                    $sqlAction      = "UPDATE";
    	                $blockToCheck   = $row["Block"];
    	                $previousHeader = $row["Header"];
    	                $previousInfo   = $row["Info"];
    	                $previousPic    = $row["Picture"];
    	                $previousSchool = $row["School"];
    	                $previousDepartment = $row["Department"];
    	                echo $name . "<br/>";
    	                
    	                echo "<br/>";
    	                return "UPDATE";
	                }
	                else if ( strpos($row["Name"],trim($name)) !== false ){
	                    $newName = trim($name);
	                    $sqlAction = "UPDATE";
	                    return "UPDATE";
	                }
	                else {
	                    echo "<span class='change' onclick='window.parent.keepMe(document.advisorToRun,document.nameFromSQL)' style='cursor:default'>".$name . "</span> -- <span onclick='window.parent.keepMe(document.nameFromSQL,document.advisorToRun)' style='cursor:default' class='change'>" . $row["Name"] . "</span>";
	                    echo "<button onclick='window.parent.skip(document.advisorToRun,document.nameFromSQL)'>Skip</button><button onclick='window.parent.setNewName(document.advisorToRun)'>New Name</button>";
	                    echo "<script>window.parent.set(\"".$name."\",\"".$row["Name"]."\");
        	                        document.advisorToRun = \"".$name."\";
        	                        document.nameFromSQL = \"".$row["Name"]."\";
        	                    </script>";
	                    exit;
	                }
	            }
	        }
	        // The advisor doesn't exist in the database yet!
	        $sqlAction = "INSERT";
            return "INSERT";
	    }
	    $sqlAction = "continue";
        $previousCheckComplete = true;
	    return "continue";
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
        global $block, $name, $header, $blockToCheck, $picture, $previousPic, $previousHeader, $info, $previousDepartment, $newName;
        $block          = "";
        $name           = "";
        $header         = "";
        $blockToCheck   = "";
        $picture        = "";
        $previousPic    = "";
        $previousHeader = "";
        $info           = "";
        $newName        = "";
        $previousDepartment = "";
        return;
    }
    function compareInclude($json1, $json2){
        if ( is_array($json1) === true ){
            $arr1 = $json1;
        }
        else {
            $arr1 = json_decode($json1, true);    
        }
        if ( is_array($json2) === true ){
            $arr2 = $json2;
        }
        else {
            $arr2 = json_decode($json2, true);    
        }
        if ( $arr1 == null ){
            if ( is_array($arr1) === false ){
                if ( $json1 != "" ){
                    $str1 = $json1;
                }
            }
        }
        if ( $arr2 == null ){
            if ( is_array($arr2) === false ){
                if ( $json2 != "" ){
                    $str2 = $json2;
                }
            }
        }
        if ( isset($str1) && isset($str2) ){
            return json_encode(array($str1,$str2),JSON_FORCE_OBJECT);
        }
        else if ( isset($str1) && !isset($str2) ){
            if ( in_array($str1, $arr2) === false ){
                array_push($arr2,$str1);
            }
            return json_encode($arr2,JSON_FORCE_OBJECT);
        }
        else if ( isset($str2) && !isset($str1) ){
            if ( in_array($str2, $arr1) === false ){
                array_push($arr1,$str2);
            }
            return json_encode($arr1,JSON_FORCE_OBJECT);
        }
        else if ( !isset($str1) && !isset($str2) ){
            for ( $i = 0, $n = count($arr1); $i < $n; $i++ ){
                if ( in_array($arr1[$i],$arr2) === false ){
                    array_push($arr2,$arr1[$i]);
                }
            }
            return json_encode($arr2,JSON_FORCE_OBJECT);
        }
        else {
            echo $json1 . "<br/>" . $json2 . "<br/>";
            echo $str1 . "<br/>" . $str2 . "<br/>";
            echo $arr1 . "<br/>" . $arr2 . "<br/>";
            return "failed";
        }
    }
    function compare($str1,$str2){
        if ( strlen($str1) > strlen($str2) ){
            return $str1;
        }
        else {
            return $str2;
        }
    }
?>