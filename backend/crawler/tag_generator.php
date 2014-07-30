<?
// Tag Library creates a list of log-likelihood values for each professor in the advisor database.
         
    /*
    * Formula for calculating log-likelihood
    * LL = 2((a*log(a/E1)) + b*log(b/E2))
    * a = Frequency of word in corpus 1, b = frequency of word in corpus 2
    * EX = (word total corpus X)(total incidences of word)/(total incidences of other words)
    */

    // Helps with encoding
    header('Content-Type:text/html; charset=UTF-8');
    
    // Useful algorithms
    require("../algorithm.php");
    
    ini_set('max_execution_time',9000); // This sets the maximum execution time of the script. In this case, 300 seconds
    ini_set("default_socket_timeout",9000); 

    $library = process_library_sql2();
    
    $max_len_data = $library["max_len_data"];
    $wordCount = $library["wordCount"];
    $libwords = $library["words"];
    
	$dl = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
    if (mysqli_connect_errno($dl))
        echo "Failed to connect to MySQL: " . mysqli_connect_error($dl);
    
	$sql = "SELECT *
			FROM `Advisor`";
	if(!$result = mysqli_query($dl,$sql))
		echo mysqli_error($dl);
	else{
		while($row = mysqli_fetch_array($result)){
			$tags = array();
			$data = $row["Block"] . " " . $row["Header"] . " " . $row["Department"] . " "  . $row["Name"] . " " . $row["School"] . " " . $row["Info"] . " " . $row["Blob"];
            $data = preg_replace('#<[^>]+>#',' ',$data);
            $data = str_replace('-',' ',$data);
            $data = utf8_encode($data);

		    // Prep the text
            $prepped_text = prep($data);
            $profWords = $prepped_text[0];
            $totalProfWords = $prepped_text[1];
            
            // Go through the words
			foreach($profWords as $word=>$freq){
				
				// Get a
				$a = $freq;
						
				// Get b
				$b = $libwords[$word];
				
				// Get c
				$c = $totalProfWords;
				
				// Get d
				$d = $wordCount;
								
				// Get Expected Values
				$E1 = $c * ($a + $b) / ($c + $d);
				$E2 = $d * ($a + $b) / ($c + $d);
				
				// Calculate Log-Likelihood
				$LL = 2 * (($a * log($a / $E1)) + $b * log($b / $E2));
				
				// Fill in $tags
				$tags[$word] = $LL;
			}
			//print_r($tags);
			$json = mysqli_real_escape_string($dl,json_encode($tags,JSON_FORCE_OBJECT));
			
			$query = "UPDATE `Advisor`
					  SET `Tags` = '".$json."'
					  WHERE `Advisor_ID` = ".$row['Advisor_ID'];
			if(!$result0 = mysqli_query($dl,$query))
				echo "ERROR: ".$row['Name']." ".mysqli_error($dl);
			else
                echo ($row["Advisor_ID"]). " --- " . $row["Name"] . " update complete <br/>";
		}
	}
	
	mysqli_close($dl);
    echo "------------------COMPLETE-------------------- <br/> FINAL ADVISOR UPDATE: " . $row["Advisor_ID"];

	
	// This function differs from process_library_sql in that it does not divide each word's frequency by total words.
	
    function process_library_sql2() {
                
        $library = array(
        	"wordCount" => 0,
        	"max_len_data" => 0,
        	"words" => array(),
        );

        ini_set("default_socket_timeout",300); 
    
        $con = mysqli_connect("localhost","svetlana","vH8ymo=nhwM6","svetlana_Total");
        if (mysqli_connect_errno($con)){
            return "ERROR: Failed to connect to MySQL: " . mysqli_connect_error($con);
        }
        
        $query = "SELECT * FROM `Advisor`";
        $result = mysqli_query($con,$query);
        
        if ($result) {
                        
            while ($row = mysqli_fetch_array($result)){          
                $data = $row["Block"] . " " . $row["Info"] . " " . $row["Name"] . " " . $row["Department"] . " " . $row["School"] . " " . $row["Header"] . " " . $row["Blob"];
                $data = preg_replace('/<[^>]+>/',' ',$data);
                $prepped_text = prep(str_replace("-",' ',$data));
                
                $library["wordCount"] += $prepped_text[1];
                
                if ($prepped_text[1] > $library["max_len_data"]){
                    $library["max_len_data"] = $prepped_text[1];
                }
                
                foreach($prepped_text[0] as $word=>$freq){
					if(array_key_exists($word,$library["words"])){
						$library["words"][$word] += $freq; 
					}
					else
						$library["words"][$word] = $freq;
				}
            }
            
            foreach($library["words"] as $word=>$freq){
            	$library["words"][$word] = $freq;
            }
                                                
            return $library;
            
        }
        else {
            return "ERROR: " . mysqli_error($con);
        }
        
    }
?>
