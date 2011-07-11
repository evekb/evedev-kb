<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
 */
class EDKError
{
	public static $errors = array();
	public static function handler  ( $errno  , $errstr  , $errfile, $errline, $errcontext)
	{
		$output = '';
		$logfile = "cache/error.log";
		switch ($errno)
		{
			case E_ERROR:
			case E_USER_ERROR:
				$output .= "<b>ERROR</b> [$errno] $errstr<br />\n";
				break;
			case E_WARNING:
				if(ini_get('error_reporting') == 0) return;
			case E_USER_WARNING:
				$output .= "<b>WARNING</b> [$errno] $errstr<br />\n";
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$output .= "<b>NOTICE</b> [$errno] $errstr<br />\n";
				break;
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				$output .= "<b>DEPRECATED</b> [$errno] $errstr<br />\n";
				break;
			case E_STRICT:
				$output .= "<b>STRICT</b> [$errno] $errstr<br />\n";
				break;
			default:
				$output .= "Unknown error type: [$errno] $errstr<br />\n";
				break;
		}
		$output .= "Error on line $errline in file $errfile<br />\n";
		$output .= "PHP " . PHP_VERSION . " (" . PHP_OS . "), ";
		$output .= "EDK " . KB_VERSION . " " . KB_RELEASE . "<br />\n";

		$trace = debug_backtrace();
		foreach($trace as $row)
		{
			if(!isset($row["file"])) continue;
			$output .= "File: ".$row["file"].", line: ".$row["line"];
			if(isset($row["class"])) $output .= ", class: ".$row["class"];
			$output .= ", function: ".$row["function"]."<br />\n";
		}
		$output .= "<br />\n";
		
		if (ini_get('log_errors') && (error_reporting() & $errno))
			error_log(sprintf("PHP %s:  %s in %s on line %d", $errno, $errstr, $errfile, $errline));
		if(class_exists('config') && config::get('cfg_log'))
		{
			error_log(sprintf("PHP %s %s:  %s in %s on line %d\n", gmdate("Y-m-d H:i:s"), $errno, $errstr, $errfile, $errline), 3, $logfile);
			if(filesize($logfile) > 1024*1024*10)
			{
				@unlink($logfile.".old");
				rename($logfile, $logfile.".old");
			}
		}
		echo $output;
		self::$errors[] = $output;
		return true;
	}
}