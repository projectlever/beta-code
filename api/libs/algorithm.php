<?php
	
	/*$stopWords = file_get_contents("../stopWords.txt");
    $stopWords = explode("\r",$stopWords);*/

	// Original weight function
    function weight($input, $library){
        // declare array weights
		$weights = array();
				
		// prep the input
		$preppedInput = prep($input);
		
		// now prep the library
		$preppedLibrary = prep($library);
		
		// for each word in the input
		foreach($preppedInput[0] as $word => $freq){
			
			// if the word is in the library
			if ($preppedLibrary[0][$word]){
			
				// calculate density in input
				$top = $freq / $preppedInput[1];
				
				// calculate density in library
				$bottom = $preppedLibrary[0][$word] / $preppedLibrary[1];
				
				// push word => weight onto weights
				$weights[$word] = $top / $bottom;
			}
		}
		
		// error check
		if(count($weights) == 0)
			return false;
		
		// return weights which is array word => weights
		return $weights;
	}
	
	// Returns array(word => word frequency, word count)
	function prep($input){
        // make the whole string lower case
        $input = strtolower($input);
        
		// break input into array of words
		$input = str_word_count($input, 1);

		// count number of words in input
		// break array of words_ into associated array word=>frequency
		$inputFreq = array_count_values($input);
		
		return array($inputFreq, $inputCount);
	}
	
	// Returns array of unique words
	function simplePrep($input){
		// list of stopwords
		// global $stopWords;
		
		// what you'll be returning
		$words = array();
		
		// make the whole string lower case
        $input = strtolower($input);
        
		// break input into array of words
		$input = str_word_count($input, 1);
				
		for($i = 0, $n = count($input); $i < $n; $i++){
			if(!in_array($input[$i],$words)){
				//if(!in_array($input[$i],$stopWords))
					array_push($words,$input[$i]);
			}
		}

		return $words;
	}
	
	function prePreppedWeight($input,$library){
		// declare array weights
		$weights = array();
				
		// prep the input
		$preppedInput = prep($input);
				
		// for each word in the input
		foreach($preppedInput[0] as $word => $freq){
			
			// if the word is in the library
			if (array_key_exists($word,$library)){
							
				// push word => weight onto weights
				
				// Algorithm 1
				 $weights[$word] = $freq / $preppedInput[1] / $library[$word];
				// Algorithm 2
				//$weights[$word] = ($freq * $freq) / $preppedInput[1] / $library[$word];
			}
		}
		
		// error check
		if(count($weights) == 0)
			return false;
		
		// return weights which is array word => weights
		return $weights;
	}
?>