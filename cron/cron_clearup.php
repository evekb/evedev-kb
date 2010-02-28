<?php

if(file_exists(getcwd().'/cron_clearup.php'))
{
	// current working directory minus last 5 letters of string ("/cron")
	$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
}
elseif(file_exists(__FILE__))
{
	$KB_HOME = preg_replace('/[\/\\\\]cron[\/\\\\]cron_clearup\.php$/', '', __FILE__);
}
else die("Set \$KB_HOME to the killboard root in cron/cron_clearup.php.");

// If the above doesn't work - place your working directory path to killboard root below - comment out the above two lines and uncomment the two below

// Edit the path below with your webspace directory to the killboard root folder - also check your php folder is correct as defined by the first line of this file
//$KB_HOME = "/home/yoursite/public_html/kb";

chdir($KB_HOME);

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/class.config.php');
require_once('common/includes/db.php');

remove_old(7 * 24, KB_QUERYCACHEDIR.'/');
remove_old(7 * 24, KB_PAGECACHEDIR.'/'.KB_SITE.'/', true);
remove_old(1 * 24, KB_CACHEDIR."/templates_c/", true);
remove_old(7 * 24, KB_MAILCACHEDIR.'/');
remove_old(30 * 24, KB_CACHEDIR.'/', true);

//! Remove old files from the given directory.

/*! \param $hours The oldest a file can be before being removed.
 *  \param $dir The directory to remove files from.
 *  \param $recurse Whether to clear subdirectories.
 */
function remove_old($hours, $dir, $recurse = false)
{
	if(!is_dir($dir)) return 0;
	$seconds = $hours*60*60;
	$del = 0;
	$files = scandir($dir);
	if(!$files)
	{
		echo "Directory invalid: ".$dir."<br>\n";
		return 0;
	}
	echo $dir."<br>".$hours." hours<br>\n";
	foreach ($files as $num => $fname)
	{
		if (file_exists("{$dir}{$fname}") && !is_dir("{$dir}{$fname}") && substr($fname,0,1) != "." && ((time() - filemtime("{$dir}{$fname}")) > $seconds))
		{
			$mod_time = filemtime("{$dir}{$fname}");
			if (unlink("{$dir}{$fname}"))
			{
				$del = $del + 1; 
				echo "Deleted: {$del} - {$fname} --- ".(round((time()-$mod_time)/3600))." hours old<br>\n";
			}
		}
		// Clear subdirectories if $recurse is true.
		if ($recurse && file_exists("{$dir}{$fname}") && is_dir("{$dir}{$fname}")
			 && substr($fname,0,1) != "." && $fname != "..")
		{
			remove_old($hours, $dir.$fname."/", $recurse);
		}
	}
}