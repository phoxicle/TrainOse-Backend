<?php

class Logger {

	private static $logFile = 'log.txt';

	public static function log($message) {
		$fh = fopen(self::$logFile,'a');
		fwrite($fh,$message . "\n");
		fclose($fh);
	}

}

?>
