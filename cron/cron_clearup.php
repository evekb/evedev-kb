<?php

$KB_HOME = preg_replace('/[\/\\\\]cron$/', '', getcwd());
chdir($KB_HOME);

remove_old(1 * 24, "cache/cache/", true);
remove_old(1 * 24, "cache/templates_c/");
remove_old(7 * 24, "cache/mails/");
remove_old(30 * 24, "cache/", true);


function remove_old($hours, $dir, $recurse = false)
{
	$seconds = $hours*60*60;

	$files = scandir($dir);

//	echo $dir."<br>".$hours." hours<br>\n";
	foreach ($files as $num => $fname)
	{
		if (file_exists("{$dir}{$fname}") && !is_dir("{$dir}{$fname}") && substr($fname,0,1) != "." && ((time() - filemtime("{$dir}{$fname}")) > $seconds))
		{
			$mod_time = filemtime("{$dir}{$fname}");
			if (unlink("{$dir}{$fname}"))
			{
				$del = $del + 1; 
//				echo "Deleted: {$del} - {$fname} --- ".(round((time()-$mod_time)/3600))." hours old<br>\n";
			}
		}
		// Clear subdirectories if $recurse is true.
		if ($recurse && file_exists("{$dir}{$fname}") && is_dir("{$dir}{$fname}")
			 && substr($fname,0,1) != "." && $fname != "..")
		{
			remove_old($hours, $dir.$fname."/");
		}
	}
}
?>