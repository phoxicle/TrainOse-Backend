<?php

//http://tickets.trainose.gr/dromologia/touch_seats.html?c=krathsh_wt&op=trip_available_seats&trip=56|ΑΘΗΝ|ΘΕΣΣ|20110210|19.29|20110210|23.55|11:&lang=gr

$from = $_GET['from']; // Αθήνα
$to = $_GET['to']; // Λάρισα
$depart = $_GET['depart']; // 07:55
$arrive = $_GET['arrive']; // 07:55
$trainNum = $_GET['trainNum']; // 1054

require('crackCode.php'); // $fromCode and $toCode

$depart = str_replace(':','.',$depart);
$arrive = str_replace(':','.',$arrive);
$departDate = date('Ymd');
$arriveDate = ($depart < $arrive) ? $departDate : date('Ymd', strtotime('+1 day'));

$tripStr = implode('|', array($trainNum, $fromCode, $toCode, $departDate,
		$depart, $arriveDate, $arrive));
$targetPage = 'http://tickets.trainose.gr/dromologia/touch_seats.html?c=krathsh_wt&op=trip_available_seats&trip='
		. urlencode($tripStr) . '&lang=gr';

header('Content-Type: text/html; charset=UTF-8');
header('Location: ' . $targetPage);

?>
