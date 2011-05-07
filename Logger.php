<?php

class Logger {

	protected static $logFile = 'log.txt';
	protected static $dbName = 'services';
	protected static $dbUser = 'root';
	protected static $dbPassword = 'root';
	
	protected static $dbTable = 'com_pheide_trainose';

	public static function logToFile($message) {
		$fh = fopen(self::$logFile,'a');
		fwrite($fh,$message . "\n");
		fclose($fh);
	}
	
	public static function logToDatabase($source, $destination, $numRoutes, $cached, $data, $url = '', $level = 0) {
		mysql_connect("localhost", self::$dbUser, self::$dbPassword) or die(mysql_error());
		mysql_select_db(self::$dbName) or die(mysql_error());
		
		$query = "INSERT INTO " . self::$dbTable . " SET"
			. " tstamp = NOW() "
			. ", severity = " . self::cleanInt($level)
			. ", data = '" . self::cleanString($data) . "'"
			. ", source = " . self::cleanString($source)
			. ", destination = " . self::cleanString($destination)
			. ", num_routes = " . self::cleanInt($numRoutes)
			. ", cached = " . self::cleanInt($cached) 
		. ";";

		if (!mysql_query($query)) {
			self::logToFile($query . "\n" . mysql_error());
		}
	}
	
	protected static function clean($data) {
		return substr(mysql_real_escape_string($data),0,10000);
	}
	
	protected static function cleanString($data) {
		return '"' . self::clean($data) . '"';
	}
	
	protected static function cleanInt($data) {
		return (int)self::clean($data);
	}

}

?>
