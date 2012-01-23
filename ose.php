<?php

require('crackCode.php');
require('Logger.php');

header('Content-type: text/xml');

$logEntry = array();

$from = $_GET['from'];
$to = $_GET['to'];
$noCache = $_GET['no_cache'];

$logEntry['source'] = $from;
$logEntry['destination'] = $to;

// Check if already cached
$cacheFilePath = 'cache/' . date('Ymd') .'_'. $from .'_'. $to .'.xml';
if (!$noCache && file_exists($cacheFilePath)) {
	$data = file_get_contents($cacheFilePath);
	
	$logEntry['cached'] = 1;
	$logEntry['data'] = $data;
	logEntry($logEntry);
	
	echo $data;
	die();
}

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
	
	//note: only take first segment
	$trainNum = $route->segments[0]->treno;
	$train = determineTrainType($trainNum);

	$routesArr[] = array(
		'duration' => $route->ttt,
		'train' => $train, 
		'trainNum' => $trainNum,
		'depart' => formatTime($route->segments[0]->wra1),
		'arrive' => formatTime($route->segments[0]->wra2),
		'delay' => $route->segments[0]->delay,
	);
}

$numRoutes = sizeof($routesArr);
$logEntry['numRoutes'] = $numRoutes;
$logEntry['severity'] = $numRoutes == 0 ? 1 : 0; // possible error if no routes

$routesXml = '<xml><routes>';
foreach ($routesArr as $route) {
	$routesXml .= '
	<route>
		<train>'.$route['train'].'</train>
		<trainNum>'.$route['trainNum'].'</trainNum>
		<depart>'.$route['depart'].'</depart>
		<arrive>'.$route['arrive'].'</arrive>
		<duration>'.$route['duration'].'</duration>
		<stops>'.'TODO'.'</stops>
		<delay>'.$route['delay'].'</delay>
		<price>'.'TODO'.'</price>
	</route>';
}
$routesXml .= '</routes></xml>';

$logEntry['data'] = $routesXml;

// Cache result
$fh = fopen($cacheFilePath, 'w');
fwrite($fh, $routesXml);
fclose($fh);

logEntry($logEntry);

// Return XML
echo $routesXml;
die();
//EOF

function formatTime($time) {$m = $time;
	list($hour,$min) = explode('.',$time);
	while (strlen($hour) < 2) $hour = '0' . $hour;
	while (strlen($min) < 2) $min = $min . '0';
	return $hour . ':' . $min;
}

function logEntry($logEntry) {
	return Logger::logToDatabase($logEntry['source'],$logEntry['destination'],$logEntry['numRoutes'],
			$logEntry['cached'],$logEntry['data'],$logEntry['url'],$logEntry['severity']);
}

function determineTrainType($trainNum) {
	$train = '';
	
	if (($trainNum > 1530 && $trainNum < 1560) || ($trainNum > 1680 && $trainNum < 1690) || ($trainNum > 2530 && $trainNum < 2560)) {
		$train = 'ΑΠΛ'; // Κοινή αμαξοστοιχία
	} else if (($trainNum > 1560 && $trainNum < 2530) || $trainNum > 2560 && $trainNum < 4300 ) { //array(1590,1592,1594,1596,1598,2590,2594,2598,3590)
		$train = 'ΠΡΟ'; // Ηλεκτροκίνητο
	} else if ( ($trainNum >= 610 && $trainNum < 620 || ($trainNum > 880 && $trainNum < 890) || $trainNum >= 4300 ) {
		$train = 'DES'; // (need to translate, don't know what it is yet)
	} else if (in_array($trainNum,array(50,51,52,53,54,55,56,57,58,59,60,61,70,71,74,75,90,91))) {
		$train = 'IC'; // Intercity
	} else if (in_array($trainNum,array(500,501,502,503))) {
		$train = 'ΤΑΧ'; // Ταχεία προτεραιότητας
	} else if (in_array($trainNum,array(604,605))) {
		$train = 'ΜΙΚ'; //	Μικτό προτεραιότητος
	} else if (in_array($trainNum,array(504,505))) {
		$train = 'ΚΛΙ'; // Κλινοθέσιο (έχει καταργηθεί)
	} else if (in_array($trainNum,array(561,590,591,592))) {
		$train = 'ΑΠΛ'; // Κοινή αμαξοστοιχία
	} else {
		$train = '';
	}
	
	return $train;
}


?>
