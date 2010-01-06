<?php

$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
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