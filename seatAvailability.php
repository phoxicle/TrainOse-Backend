<?php

// http://tickets.trainose.gr/dromologia/i.php?c=krathsh&ekpt50=0&ekpt25=0&trip=50|ΑΘΗΝ|ΘΕΣΣ|20121210|7.18|20121210|12.41

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
$targetPage = 'http://tickets.trainose.gr/dromologia/i.php?c=krathsh&ekpt50=0&ekpt25=0&trip='
		. urlencode($tripStr);

header('Content-Type: text/html; charset=UTF-8');
header('Location: ' . $targetPage);

?>
