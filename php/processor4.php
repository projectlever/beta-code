<?php
/*
 * Calculates TFxIDF for each word of each professor
 * TF = .5 + (.5 * frequency(t,d)) / (max(w,d))
 * IDF = log(D / (total(t,D))
 * D = total number of documents
 * total(t,D) = total number of documents containing that term
 * f(t,d) = frequency of term in document
 * max(w,d) = maximum frequency of any term in document
 */

// Helps with encoding
header('Content-Type:text/html; charset=UTF-8');

// Useful algorithms
require("algorithm.php");

ini_set('max_execution_time', 90000); // This sets the maximum execution time of the script. 
ini_set("default_socket_timeout", 90000);

$dl = mysqli_connect("localhost", "root", "thegreat", "svetlana_Total");
if (mysqli_connect_errno($dl))
    echo "Failed to connect to MySQL: " . mysqli_connect_error($dl);

$categories = array(
    "Extracurricular" => array("Name","Description"));
//"Advisor" => array("Header","Department","Name","School","Info","Blob","Block"),
//    "Course" => array(
//        "School",
//        "Department",
//        "Name",
//        "Description",
//        "Faculty"
//    ),
//  "Thesis" => array(
//        "School",
//        "Department",
//        "Name",
//        "Author",
//        "Abstract",
//        "Advisor1",
//        "Subject"
//    ),
//    "Grant" => array(
//        "Name",
//        "Description",
//        "Info",
//        "Amount",
//        "Expenses",
//        "Class",
//        "Location",
//        "Purpose",
//        "Field_of_Study",
//        "Concentration",
//        "Citizenship",
//        "Sponsor",
//       "Blob"
//    ),
//    "Funding" => array(
//        "Name",
//        "School",
//        "Abstract",
//        "FirstNamePI",
//        "MiddleNamePI",
//        "LastNamePI",
//        "ProgramManager",
//        "Co-PINames"
//    )
    //"Grad" => array("Name","Tagline","Bio","ResearchInterests","Department","School","Education"),
// );

foreach ($categories as $type => $value) {
    
    echo "Building the " . $type . " library...<br/>";
    $library = process_library($type, $categories[$type]);
    echo "Finished the " . $type . " library...<br/>";
    $sql = "SELECT *
                FROM `" . $type . "`";
    if (!$result = mysqli_query($dl, $sql))
        echo mysqli_error($dl);
    else {
        while ($row = mysqli_fetch_array($result)) {
            $wordMax = 0;
            $tags    = array();
            $data    = "";
            foreach ($categories[$type] as $x) {
                $data .= " " . $row[$x];
            }
            
            $data = preg_replace('#<[^>]+>#', ' ', $data);
            $data = str_replace('-', ' ', $data);
            $data = utf8_encode($data);
            
            // Prep the text
            $prepped_text   = prep($data);
            $profWords      = $prepped_text[0];
            $totalProfWords = $prepped_text[1];
            
            // Go through the words
            foreach ($profWords as $word => $freq) {
                
                // Get f(t,d)
                $tags[$word] = $freq;
                
                // Check max
                if ($freq >= $wordMax)
                    $wordMax = $freq;
            }
            
            // Calculate TF
            foreach ($tags as $word => $freq) {
                $tf          = .5 + (.5 * $freq) / ($wordMax);
                
                $tags[$word] = $tf * $library[$word];
            }
            
            // Sort it for my own sanity
            arsort($tags);
            
            $json = mysqli_real_escape_string($dl, json_encode($tags, JSON_FORCE_OBJECT));
            
            $query = "UPDATE `" . $type . "`
                          SET `Tags` = '" . $json . "'
                          WHERE `" . $type . "_ID` = " . $row[$type . '_ID'];
            
            if (!$result0 = mysqli_query($dl, $query))
                echo "ERROR: " . $row['Name'] . " " . mysqli_error($dl);
            else
                echo ($row[$type . "_ID"]) . " --- " . $row["Name"] . " update complete <br/>";
        }
    }
    echo "-----------------" . $type . " Complete----------------<br/>";
}

mysqli_close($dl);
echo "------------------COMPLETE-------------------- <br/>";


// This function differs from process_library_sql in that it does not divide each word's frequency by total words.

function process_library($type, $dataArray)
{
    // word => IDF
    $idf = array();
    
    // word => total(t,D)
    $library = array();
    
    // Total documents
    $D = 0;
    
    ini_set("default_socket_timeout", 300);
    
    $con = mysqli_connect("localhost", "root", "thegreat", "svetlana_Total");
    if (mysqli_connect_errno($con)) {
        return "ERROR: Failed to connect to MySQL: " . mysqli_connect_error($con);
    }
    
    $query  = "SELECT * FROM `" . $type . "`";
    $result = mysqli_query($con, $query);
    
    if ($result) {
        // Get D
        $D = mysqli_num_rows($result);
        
        while ($row = mysqli_fetch_array($result)) {
            // Reset data
            $data = "";
            
            // Concatenate all the columns
            foreach ($dataArray as $x) {
                $data .= " " . $row[$x];
            }
            
            // Clean data and generate list of unique words
            $data         = preg_replace('/<[^>]+>/', ' ', $data);
            $prepped_text = simplePrep(str_replace("-", ' ', $data));
            
            // Add those words to library or increment d
            foreach ($prepped_text as $word) {
                if ($library[$word]) {
                    $library[$word] += 1;
                } else {
                    $library[$word] = 1;
                }
            }
        }
        
        foreach ($library as $word => $d) {
            $idf[$word] = log($D / $d);
        }
        
        return $idf;
        
    } else {
        return "ERROR: " . mysqli_error($con);
    }
    
}
?>