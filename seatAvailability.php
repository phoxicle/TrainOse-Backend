<?php

// Old (one leg only)
// http://tickets.trainose.gr/dromologia/i.php?c=krathsh&ekpt50=0&ekpt25=0&trip=50|ΑΘΗΝ|ΘΕΣΣ|20121210|7.18|20121210|12.41

// New (multiple legs)
// http://tickets.trainose.gr/dromologia/i.php?c=krathsh&enhlikes=1&ekpt50=0&ekpt25=0&trip=2575|ΒΟΛΟ|ΛΑΡ|20121211|21.25|20121211|22.13:60|ΛΑΡ|ΘΕΣΣ|20121211|22.19|20121211|23.41

// http://www.pheide.com/Services/TrainOse/seatAvailability_new.php?from[]=Volos&to[]=Larissa&depart[]=17:25&arrive[]=18:13&trainNum[]=2571&from[]=Larissa&to[]=Athens&depart[]=19:26&arrive[]=23:24&trainNum[]=61
// http://www.pheide.com/Services/TrainOse/seatAvailability_new.php?from=Volos&to=Larissa&depart=17:25&arrive=18:13&trainNum=2571



// In NEW version, these are arrays!

$from = $_GET['from']; // Αθήνα
$to = $_GET['to']; // Λάρισα
$depart = $_GET['depart']; // 07:55
$arrive = $_GET['arrive']; // 07:55
$trainNum = $_GET['trainNum']; // 1054

require('crackCode_funcs.php'); // provides getCodeFromStation()

$tripStr = '';

if (is_array($from)) {
	
	$trips = array();

	for ($i=0; $i < count($from); $i++) {
		
		// Language handling has changed on target page, so everyone well get Greek...
		
		list($fromCode, $lang) = getCodeFromStation($from[$i]);
		list($toCode, $lang) = getCodeFromStation($to[$i]);
		
		$trips[] = leg_string($fromCode, $toCode, $depart[$i], $arrive[$i], $trainNum[$i]);
	}

	$tripStr = implode(':', $trips);

} else {

	list($fromCode, $lang) = getCodeFromStation($from);
	list($toCode, $lang) = getCodeFromStation($to);

	$tripStr .= leg_string($fromCode, $toCode, $depart, $arrive, $trainNum);
}

$targetPage = 'http://tickets.trainose.gr/dromologia/i.php?c=krathsh&ekpt50=0&ekpt25=0&trip='
		. urlencode($tripStr);

header('Content-Type: text/html; charset=UTF-8');
header('Location: ' . $targetPage);

// EOF

function leg_string($fromCode, $toCode, $depart, $arrive, $trainNum){

	$depart = str_replace(':','.',$depart);
	$arrive = str_replace(':','.',$arrive);
	$departDate = date('Ymd');
	$arriveDate = ($depart < $arrive) ? $departDate : date('Ymd', strtotime('+1 day'));

	$tripStr = implode('|', array($trainNum, $fromCode, $toCode, $departDate,
			$depart, $arriveDate, $arrive));

	return $tripStr;
}


?>
