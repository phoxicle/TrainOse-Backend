<?php

require('crackCode_funcs.php');
require('Logger.php');

$logEntry = array();

$from = $_GET['from'];
$to = $_GET['to'];
$noCache = $_GET['no_cache'];

$logEntry['source'] = $from;
$logEntry['destination'] = $to;

// Check if already cached
$cacheFilePath = 'cache/' . date('Ymd') .'_'. $from .'_'. $to .'.json';
if (!$noCache && file_exists($cacheFilePath)) {
	$data = file_get_contents($cacheFilePath);
	
	$logEntry['cached'] = 1;
	$logEntry['data'] = $data;
	logEntry($logEntry);
	
	echo $data;
	die();
}

// in crackCode.php
list($fromCode,$toCode,$lang) = getFromAndToCode($to,$from);

$params = array(
	'lang' => 'gr',
	'c' => 'dromologia',
	'op' => 'vres_dromologia',
	'travel_type' => 'metabash',
	'rtn_date' => '',
	'rtn_time' => '',
	'rtn_time_type' => '',
	'apo' => $fromCode,
	'pros' => $toCode,
	'date' => date('Y-m-d'),
	'time_type' => 'anaxwrihi',
	'time' => '23:59',
);
$additionalParams = '&trena[+]=apla&trena[+]=ic&trena[+]=ice&trena[+]=bed';
$url = 'http://tickets.trainose.gr/dromologia/ajax.php?' . http_build_query($params) . $additionalParams;

$logEntry['url'] = $url;

$jsonContents = file_get_contents($url);
$jsonArr = json_decode($jsonContents);

$routes = $jsonArr->data->metabash;
$routesArr = array();
foreach ($routes as $route) {
	
	// deal with the legs
	$legs = array();
	foreach ($route->segments as $segment) {
		$legs[] = array(
			'trainNum' => $segment->treno,
			'train' => determineTrainType($segment->treno),
			'depart' => formatTime($segment->wra1),
			'arrive' => formatTime($segment->wra2),
			'delay' => formatDelay($segment->delay),
			'source' => getStationFromCode($segment->apo,$lang),
			'destination' => getStationFromCode($segment->ews,$lang),
		);
	}
	
	$routesArr[] = array(
		'duration' => $route->ttt,
		'legs' => $legs,
	);
}

$numRoutes = sizeof($routesArr);
$logEntry['numRoutes'] = $numRoutes;
$logEntry['severity'] = $numRoutes == 0 ? 1 : 0; // possible error if no routes

$routesJson = json_encode(array('routes' => $routesArr));

$logEntry['data'] = $routesJson;

// Cache result
$fh = fopen($cacheFilePath, 'w');
fwrite($fh, $routesJson);
fclose($fh);

logEntry($logEntry);

// Return JSON
echo $routesJson;
die();
//EOF

function formatTime($time) {$m = $time;
	list($hour,$min) = explode('.',$time);
	while (strlen($hour) < 2) $hour = '0' . $hour;
	while (strlen($min) < 2) $min = $min . '0';
	return $hour . ':' . $min;
}


// .04 => +4', .55 => +55', 1.02 => +62'
function formatDelay($delay) {
	if ($delay) {
		list($dh,$dm) = explode('.',$delay);
		return '+' . ( 60*$dh + $dm ) . '\'';
	} else return "";
}

function logEntry($logEntry) {
	return Logger::logToDatabase($logEntry['source'],$logEntry['destination'],$logEntry['numRoutes'],
			$logEntry['cached'],$logEntry['data'],$logEntry['url'],$logEntry['severity']);
}

function determineTrainType($trainNum) {
	$train = '';
	
	if ($trainNum >= 50 && $trainNum < 100) {
		$train = 'IC'; // Intercity
	} else if ($trainNum >= 500 && $trainNum < 510) {
		$train = 'ΤΑΧ'; // Ταχεία προτεραιότητας
	} else if (($trainNum >= 610 && $trainNum < 620) || ($trainNum >= 880 && $trainNum < 890)) {
		$train = 'DES'; // (need to translate, don't know what it is yet)
	} else if (($trainNum >= 560 && $trainNum < 600) || ($trainNum >= 730 && $trainNum < 760)   ||
			($trainNum >= 1380 && $trainNum < 1560)  || ($trainNum >= 1680 && $trainNum < 1690) ||
			($trainNum >= 2530 && $trainNum < 2580)  || ($trainNum >= 3380 && $trainNum < 3530) ||
			($trainNum >= 3730 && $trainNum < 3750)  || ($trainNum >= 1680 && $trainNum < 1690) ||
			(in_array($trainNum,array('Α300','Α301','Α302','Α303','Α304','Α305','Α306','Α307','Α308','Α309'))) ||
			(in_array($trainNum,array('Β300','Β301','Β302','Β303','Β304','Β305','Β306','Β307','Β308','Β309'))) ||
			(in_array($trainNum,array('Γ300','Γ301','Γ302','Γ303','Γ304','Γ305','Γ306','Γ307','Γ308','Γ309'))) ||
			(in_array($trainNum,array('Δ300','Δ301','Δ302','Δ303','Δ304','Δ305','Δ306','Δ307','Δ308','Δ309'))) ||
			(in_array($trainNum,array('Π300','Π301','Π302','Π303','Π304','Π305','Π306','Π307','Π308','Π309')))) {
		$train = 'ΑΠΛ'; // Κοινή αμαξοστοιχία
	} else if (($trainNum >= 1590 && $trainNum < 1600) || $trainNum >= 2590 && $trainNum < 2600 || ($trainNum >= 3590 && $trainNum < 3600)) {
		$train = 'ΗΛΕ'; // Ηλεκτροκίνητο
	} else if (($trainNum >= 1300 && $trainNum < 1310) || ($trainNum >= 2300 && $trainNum < 2310) ||
		    ($trainNum >= 3300 && $trainNum < 3310) || ($trainNum >= 4300 && $trainNum < 4310) ||
			(in_array($trainNum,array('Λ49Α','Λ49Β','Λ05Α','Λ05Β')))) {
		$train = 'R/B'; // (need to translate, don't know what it is yet)
	} else if (($trainNum >= 4520 && $trainNum < 4530) || ($trainNum >= 6520 && $trainNum < 6530) ||
			(in_array($trainNum,array('Β570','Β571','Β572','Β573','Β574','Β575','Β576','Β577','Β578','Β579'))) ||
			(in_array($trainNum,array('Λ1','Λ2Ε','Λ3Ε','Λ4','Λ5','Λ6','Λ7','Λ8','Λ9','Λ10','Λ11Ε','Λ12Ε')))) {
		$train = 'ΛΕΩ'; // Λεωφορεία ΟΣΕ
	} else if (($trainNum >= 1330 && $trainNum < 1340) || ($trainNum >= 3330 && $trainNum < 3340 )) {
		$train = 'ΟΔΟ'; // Οδοντωτός
	} else if ($trainNum >= 3800 && $trainNum < 3810) {
		$train = 'ΤΟΥ'; // Τουριστικό
	} else if (in_array($trainNum,array(604,605))) {
		$train = 'ΜΙΚ'; //	Μικτό προτεραιότητος
	} else if (in_array($trainNum,array(504,505))) {
		$train = 'ΚΛΙ'; // Κλινοθέσιο (έχει καταργηθεί)
	} else {
		$train = '';
	}
	
	return $train;
}



?>
