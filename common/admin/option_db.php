<?php
/**
 * @package EDK
 */

options::cat('Maintenance', 'Database', 'Table Checks');
options::fadd('Reinstall CCP DB', 'none', 'custom', array('admin_db', 'CCPDBlink'));

class admin_db
{
	/**
	 * Create an option to link to the database upgrade page.
	 * @return string HTML link to the database upgrade page.
	 */
	function CCPDBlink()
	{
		if(!file_exists("update/CCPDB/update.php"))
			return "Database update installer is not present.";
		if(!file_exists("packages/database/kb3_dgmtypeattributes/table.xml"))
			return "Database packages are not installed.";

		return "<a href='".KB_HOST."/update/index.php?package=CCPDB&amp;do=reset'>".
			"Reinstall</a>";
	}
}
