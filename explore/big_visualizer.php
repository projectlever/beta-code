<?
	$data = json_decode(include("http://projectlever.com/advisors/magic0.php"),1);
	$data = array_slice($data["Advisor"],0,50);
	
	$departments = array();
	$weighted_topics = array();
	$topics = array();
	$professors = array();
	
	for($i = 0, $n = count($data); $i < $n; $i++){
		$professor = $data[$i];
		$name = $professor["name"];
		$ID = $professor["id"];
		$links = array();
				
		$department_unsplit = $professor["department"];
		//echo $department_unsplit;
		$department_split = explode(" - ", $department_unsplit);
		//print_r($department_split);
		for($j = 0, $m = count($department_split); $j < $m; $j++){
			if(!in_array($department_split[$j],$departments)){
				array_push($departments, $department_split[$j]);
			}
			array_push($links,$department_split[$j]);
		}
		
		//print_r($links);
		//echo $ID;exit;
		$file = file_get_contents("http://projectlever.com/advisor_viz/".$ID.".json");
		$file = json_decode($file,1);
		//print_r($file);exit;
		$nodes = $file["Nodes"];
		for($j = 0, $m = count($nodes); $j < $m; $j++){
			$word = $nodes[$j]["name"];
			$weight = $nodes[$j]["size"];
			array_push($links,$word);
			if($weighted_topics[$word]){
				$weighted_topics[$word] = $weighted_topics[$word] + 1;
			}
			else{
				$weighted_topics[$word] = 1;
			}
		}
		//print_r($nodes);
		
		//print_r($links);
		
		$prof = array(
					"type"=>"professor",
					"name"=>$name,
					"description"=>"",
					"episode"=>"Profile",
					"slug"=>"/single_advisor_display.php?id=".$ID,
					"links"=>$links,
			);
		array_push($professors,$prof);
	}
	
	
	arsort($weighted_topics);
	foreach($weighted_topics as $word=>$count){
		if($count == 1){
			unset($weighted_topics[$word]);
		}
	}
	//print_r($weighted_topics);exit;
	sort($departments);
	//print_r($departments);
	//print_r($data);
	
	//$weighted_topics = array_slice($weighted_topics,0,30);
	foreach($weighted_topics as $word=>$weight){
		$top = array(
			"type"=>"topic",
			"name"=>$word,
		);
		array_push($topics,$top);
	}
	//print_r($weighted_topics);exit;
	
	$departments_final = array();
	for($i = 0, $n = count($departments); $i < $n; $i++){
		$dpt = array(
			"type"=>"department",
			"name"=>$departments[$i],
		);
		array_push($departments_final,$dpt);
	}
		
	for($i = 0, $n = count($professors); $i < $n; $i++){
		$links = $professors[$i]["links"];
		for($j = 0, $m = count($links); $j < $m; $j++){
			if(!in_array($links[$j],$departments) && !$weighted_topics[$links[$j]]){
				//echo $links[$j]."<br>";
				unset($professors[$i]["links"][$j]);
			}
		}
		$professors[$i]["links"] = array_values($professors[$i]["links"]);
	}
	
	
	//print_r($departments_final);
	//print_r($topics);
	//print_r($professors);
	
	$json = array(
		"professors" => $professors,
		"departments" => $departments_final,
		"topics" => $topics,
	);

	$json = json_encode($json);
	echo $json;	
?>
