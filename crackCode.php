<?php

$from = $_GET['from'];
$to = $_GET['to'];

// Get code for to/from
require('trainose_sdata.php');
$trainData = json_decode($sta8moi); // imported from trainose_sdata
$toCode = '';
$fromCode = '';
foreach ($trainData as $station) {
	if (!$toCode && ($station->gr == $to || $station->en == $to)) {
		$toCode = $station->code;	
	}
	if (!$fromCode && ($station->gr == $from || $station->en == $from)) {
		$fromCode = $station->code;
	}
	
	if ($toCode && $fromCode) break;
}


?>

