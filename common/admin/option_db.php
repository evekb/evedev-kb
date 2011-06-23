<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

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
		$size = self::size(KB_QUERYCACHEDIR);
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
		CacheHandler::removeBySize("SQL", 1);
	}
	//! Create an option to link to the database upgrade page.
	function CCPDBlink()
	{
		if(!file_exists("update/CCPDB/update.php"))
			return "Database update installer is not present.";
		if(!file_exists("packages/database/kb3_dgmtypeattributes/table.xml"))
			return "Database packages are not installed.";

		return "<a href='".KB_HOST."/update/index.php?package=CCPDB&amp;do=reset'>".
			"Reinstall</a>";
	}
	private static function size($dir = null)
	{
		if(is_null($dir)) return 0;
		if(!is_dir($dir)) return filesize($dir);
		$size = 0;
		$files = scandir($dir);
		foreach ($files as $file)
			if (substr($file, 0, 1) != '.')
				$size += self::size($dir.'/'.$file);

		return $size;
	}
}
