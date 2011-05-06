<?php

$logContents = file_get_contents('log.txt');

// Prepare routes arr
$routesArr = array();
$routeStrArr = explode("\n\n",$logContents);

foreach ($routeStrArr as $routeStr) {
	$routeArr = array();
	
	$routeStrLines = explode("\n",$routeStr);

	// First line: date
	list($routeArr['date'],) = explode('T',$routeStrLines[0]);
	if (!is_numeric($routeArr['date'][0])) continue; 
	$routeArr['datetime'] = $routeStrLines[0];
	
	// Second line: source/destination
	$sourceDestLine = $routeStrLines[1];
	$sourceStartPos = strlen('Request for route') + 1;
	$arrowPos = strpos($sourceDestLine,'=>');
	$source = substr($sourceDestLine,$sourceStartPos,$arrowPos - $sourceStartPos - 1);
	$destination = substr($sourceDestLine,$arrowPos + 3);
	$routeArr['source'] = $source;
	$routeArr['destination'] = $destination;
	
	// Cached route
	if (strpos($routeStrLines[2],'cached') !== false) {
		$routeArr['cached'] = true;
		$routeArr['numRoutes'] = '';
	} else {
		$routeArr['cached'] = false;
		
		// Fourth line: num routes
		$numRoutesLine = $routeStrLines[3];
		$routeArr['numRoutes'] = (int)substr($numRoutesLine,strlen('Found') + 1,2); 
	}
	
	$routesArr[] = $routeArr;
}

//Export list as csv
if ($_GET['export']) {
	$fp = fopen('stats.csv', 'w');
	foreach ($routesArr as $fields) {
	    fputcsv($fp, $fields); // comma separated
	}
	fclose($fp);
}

// Print some stats

$numPerPair = array(); // Number of times each route was downloaded
$numRoutesPerPair = array(); // Number of routes in each source/dest pair
$numPerDay = array(); // Number of requests per day
$numPerDaySplit = array(); // Number of requests per day, separated by cached/noncached
$numPerDayNoncached = array(); // Number of requests per day that were noncached
$numPerDatetime = array(); // Number of routes per second

foreach ($routesArr as $route) {
	$numPerPair[$route['source']][$route['destination']]++;
	
	if (!$route['cached']) {
		$numRoutesPerPair[$route['source']][$route['destination']][] = $route['numRoutes'];
		
		$numPerDaySplit[$route['date']]['non-cached']++;
		$numPerDayNoncached[$route['date']]++;
	} else {
		$numPerDaySplit[$route['date']]['cached']++;
	}
	$numPerDay[$route['date']]++;
	$numPerDatetime[$route['datetime']]++;
	$numPerDaySplit[$route['date']]['total']++;
}

//echo "num per pair:\n";print_r($numPerPair);
echo "num routes per pair:\n";print_r($numRoutesPerPair);
//echo "num per day:\n";print_r($numPerDay);
//echo "num per day split:\n";print_r($numPerDaySplit);
//echo "num per day noncached:\n";print_r($numPerDayNoncached);
//echo "num per datetime:\n";print_r($numPerDatetime);

