<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// include all admin modules
// this doesnt need to check for itself because its already loaded
$dir = 'common/admin/';
if (is_dir($dir))
{
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            // only load auto-option files
            if (strstr($file, 'option_') && substr($file, -4) == '.php')
            {
                require_once($dir.$file);
            }
        }
        closedir($dh);
    }
}

// load all auto-options from mods
$mods_active = explode(',', config::get('mods_active'));
$modOverrides = false;
foreach ($mods_active as $mod)
{
    if (file_exists('mods/'.$mod.'/auto_settings.php'))
    {
        include('mods/'.$mod.'/auto_settings.php');
    }
}

/**
 * Overload the box object to force every admin page to use the new options menu
 * @package EDK
 */
class Box2 extends Box
{
    function generate()
    {
        return options::genAdminMenu();
    }
}

$menubox = new Box2();

options::oldMenu('Features', "Campaigns", array(array('a', 'admin_cc',  true),
	array('op', 'view',  false)));
options::oldMenu('Features', "Standings", array('a', 'admin_standings',  true));//

options::oldMenu('Appearance', "Map Options", array('a', 'admin_mapoptions', true));

options::oldMenu('Advanced', "Post Permissions", array('a', 'admin_postperm', true));
options::oldMenu('Advanced', "Item Values", array('a', 'admin_value_fetch', true));

options::oldMenu('Features', "Modules", array('a', 'admin_mods', true));

options::oldMenu('Features', "Feed Syndication", array('a', 'admin_idfeedsyndication', true));
options::oldMenu('Features', "API Killlog", array('a', 'admin_api', true));
options::oldMenu('Features', "Old Feed", array('a', 'admin_feedsyndication', true));

options::oldMenu('Maintenance', "Auditing", array('a', 'admin_audit', true));
options::oldMenu('Maintenance', "Troubleshooting", array('a', 'admin_troubleshooting', true));
options::oldMenu('Maintenance', "File Verification", array('a', 'admin_verify', true));
options::oldMenu('Maintenance', "Upgrade", array('a', 'admin_upgrade', true));
options::oldMenu('Maintenance', "Settings Report", array('a', 'admin_status', true));
options::oldMenu('Kill Import/Export', "Kill Import - files", array('a', 'admin_kill_import', true));
options::oldMenu('Kill Import/Export', "Kill Import - csv", array('a', 'admin_kill_import_csv', true));
options::oldMenu('Kill Import/Export', "Kill Export - files", array('a', 'admin_kill_export', true));
//options::oldMenu('Kill Import/Export', "Kill Export - csv", array('a', 'admin_kill_export_search', true));
options::oldMenu('- Logout -', "Logout", array('a', 'logout', true));

#options::oldMenu('User', 'Titles', '?a=admin_titles');

options::oldMenu('Appearance', "Top Navigation", array('a', 'admin_navmanager', true));
