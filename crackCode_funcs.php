<?php

require('trainose_sdata.php');
$trainData = json_decode($sta8moi); // imported from trainose_sdata

function getFromAndToCode($to,$from) {
	global $trainData;

	// Get code for to/from
	$toCode = '';
	$fromCode = '';
	foreach ($trainData as $station) {
		if (!$toCode) {
			if ($station->gr == $to) {
				$toCode = $station->code;
				$lang = 'gr';
			}
			else if ($station->en == $to) {
				$toCode = $station->code;
				$lang = 'en';
			}	
		}
		if (!$fromCode) {
			if ($station->gr == $from) {
				$fromCode = $station->code;
				$lang = 'gr';
			} else if ($station->en == $from) {
				$fromCode = $station->code;
				$lang = 'en';
			}	
		}
	
		if ($toCode && $fromCode) break;
	}
	return array($fromCode,$toCode,$lang);
}

function getCodeFromStation($name) {
	global $trainData;

	// Get code
	$code = '';
	foreach ($trainData as $station) {
		
		if ($station->gr == $name) {
			$code = $station->code;
			$lang = 'gr';
		}
		else if ($station->en == $name) {
			$code = $station->code;
			$lang = 'en';
		}	
	}	

	return array($code, $lang);
}

function getStationFromCode($code,$lang) {
	global $trainData;
	foreach ($trainData as $station) {
		if ($code == $station->code)
			if ($lang == 'en')
				return $station->en;
			else
				return $station->gr;
	}
}


?>
