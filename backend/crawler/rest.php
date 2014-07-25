<?php

// Put your User GUID here
$userGuid = "c7893eff-9080-463b-9815-defb79810e04";
// Put your API key here
$apiKey = "byd3h2MCJmxug2fwp5JFLXJrdrhSOye4GMGDIEJeYc1EgWEjPKFUQtL5CB7ZF7xO/zWNGwBep2CKd/Ra1zLPsQ==";

function query($connectorGuid, $input, $userGuid, $apiKey) {

	$url = "https://query.import.io/store/connector/" . $connectorGuid . "/_query?_user=" . urlencode($userGuid) . "&_apikey=" . urlencode($apiKey);

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode(array("input" => $input)));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$result = curl_exec($ch);
	curl_close($ch);

	return json_decode($result);
}

// Example of doing a query
$result = query("afce0c7f-4de2-4bc5-aab7-4f4c7badbb33", array(
	"webpage/url"=>"http://www.hds.harvard.edu/people/faculty/leila-ahmed"
), $userGuid, $apiKey);

var_dump($result);
