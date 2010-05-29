<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class EDKError
{
	public static function handler  ( $errno  , $errstr  , $errfile, $errline, $errcontext)
	{
		switch ($errno)
		{
			case E_ERROR:
			case E_USER_ERROR:
				echo "<b>ERROR</b> [$errno] $errstr<br />\n";
				break;
			case E_WARNING:
				if(ini_get('error_reporting') == 0) return;
			case E_USER_WARNING:
				echo "<b>WARNING</b> [$errno] $errstr<br />\n";
				break;
			default:
				echo "Unknown error type: [$errno] $errstr<br />\n";
				break;
		}
		echo "Error on line $errline in file $errfile<br />\n";
		echo "PHP " . PHP_VERSION . " (" . PHP_OS . "), ";
		echo "EDK " . KB_VERSION . " " . KB_RELEASE . "<br />\n";

		$trace = debug_backtrace();
		foreach($trace as $row)
		{
			if(!$row["file"]) continue;
			echo "File: ".$row["file"].", line: ".$row["line"];
			if(isset($row["class"])) echo ", class: ".$row["class"];
			echo ", function: ".$row["function"]."<br />\n";
		}
		echo "<br />\n";
		
		if (ini_get('log_errors') && (error_reporting() & $errno))
			error_log(sprintf("PHP %s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline));
		return true;
	}
}