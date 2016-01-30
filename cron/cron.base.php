<?php
/**
 * @package EDK
 */
// we want to see in the logs if something goes wrong
@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

// try to disable time limit
if(function_exists("set_time_limit"))
{
	@set_time_limit(0);
}
// try to increase memory limit
@ini_set('memory_limit', '1024M');

if (!substr_compare(PHP_OS, 'win', 0, 3, true))
{
	@ini_set('include_path', ini_get('include_path').';.\\common\\includes');
}
else
{
	@ini_set('include_path', ini_get('include_path').':./common/includes');
}

// one level up
chdir(dirname(__FILE__).DIRECTORY_SEPARATOR.'..');


require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');
require_once('common/includes/class.edkerror.php');

set_error_handler(array('EDKError', 'handler'), E_ERROR );

$config = new Config(KB_SITE);
define('KB_TITLE', config::get('cfg_kbtitle'));

if (!$dir = config::get('cache_dir'))
{
    $dir = 'cache/data';
}
if(!defined('KB_CACHEDIR'))
{
    define('KB_CACHEDIR', $dir);
}


/**
 * Prints the given logText, prefixing it with a GMT timestamp
 * and adding a newline at the end. Also replaces HTML line breaks
 * with the appropriate ones and strips HTML tags from the logText.
 * @param string $logText the text to log
 */
function logCron($logText)
{
        // determine correct line break for this environment
	$linebreak = "<br/>";
	if(defined('STDIN'))
	{
		$linebreak = PHP_EOL;
	}
	// create GMT timestamp
        $timestamp = gmdate("Y/m/d H:i:s");
	// convert any HTML linebreaks to appropriate linebreaks
        $logText = preg_replace('/<br(\s)*(\/)?>/', PHP_EOL, $logText);
	$logText = str_replace("</div>","</div>".PHP_EOL, $logText);
	// strip any remaining HTML tags
        $logText = strip_tags($logText);
	$logText = str_replace(PHP_EOL, $linebreak, $logText);
    
        print $timestamp.' - '.$logText.$linebreak;
}