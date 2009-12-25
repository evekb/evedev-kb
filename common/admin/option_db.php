<?php
options::cat('Maintenance', 'Database', 'Table Checks');
//options::fadd('This checks automatically your database', 'none', 'custom', array('admin_db', 'checkDatabase'), array('admin_db', 'none'));
options::fadd('Current SQL cache size', 'none', 'custom', array('admin_db', 'checkCache'), array('admin_db', 'killCache'));
options::fadd('Reinstall CCP DB', 'none', 'custom', array('admin_db', 'CCPDBlink'));

class admin_db
{
	function checkDatabase()
	{
	// nothing to do atm
	}

	function none()
	{
	// do nothing on submit
	}
	//! Check the size of the query cache and create a clear cache option.
	function checkCache()
	{
		$size = 0;
		$dir = opendir(KB_QUERYCACHEDIR);
		while ($line = readdir($dir))
		{
			if (strstr($line, 'qcache_qry') !== false)
			{
				$size += filesize(KB_QUERYCACHEDIR.'/'.$line);
			}
		}

		// GB
		if (($size / 1073741824) > 1)
		{
			return round($size/1073741824, 4).' GB <input type="checkbox" name="option_sql_clearcache" />Clear cache ?';
		// MB
		}elseif (($size / 1048576) > 1)
		{
			return round($size/1048576, 4).' MB <input type="checkbox" name="option_sql_clearcache" />Clear cache ?';
		// KB
		}else
		{
			return round($size/1024, 2).' KB <input type="checkbox" name="option_sql_clearcache" />Clear cache ?';
		}
	}
	//! Delete the contents of the query cache.
	function killCache()
	{
		if ($_POST['option_sql_clearcache'] != 'on')
		{
			return;
		}

		$dir = opendir(KB_QUERYCACHEDIR);
		while ($line = readdir($dir))
		{
			if (strstr($line, 'qcache_qry') !== false)
			{
				@unlink(KB_QUERYCACHEDIR.'/'.$line);
			}
			elseif (strstr($line, 'qcache_tbl') !== false)
			{
				@unlink(KB_QUERYCACHEDIR.'/'.$line);
			}
		}
	}
	//! Create an option to link to the database upgrade page.
	function CCPDBlink()
	{
		if(!file_exists("update/CCPDB/update.php"))
			return "Database update installer is not present.";
		if(!file_exists("packages/database/kb3_dgmtypeattributes/table.xml"))
			return "Database packages are not installed.";

		return "<a href='".KB_HOST."/update/index.php?package=CCPDB&do=reset'>".
			"Reinstall</a>";
	}
}
