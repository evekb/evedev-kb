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

//options::oldMenu('Features', "Contracts", KB_HOST."/?a=admin_cc&amp;op=view&amp;type=contract");
options::oldMenu('Features', "Campaigns", KB_HOST."/?a=admin_cc&amp;op=view&amp;type=campaign");
options::oldMenu('Features', "Standings", KB_HOST."/?a=admin_standings");

options::oldMenu('Appearance', "Map Options", KB_HOST."/?a=admin_mapoptions");

options::oldMenu('Advanced', "Post Permissions", KB_HOST."/?a=admin_postperm");
options::oldMenu('Advanced', "Item Values", KB_HOST."/?a=admin_value_fetch");

options::oldMenu('Features', "Modules", KB_HOST."/?a=admin_mods");

options::oldMenu('Features', "Feed Syndication", KB_HOST."/?a=admin_feedsyndication");
options::oldMenu('Features', "IDFeed Syndication", KB_HOST."/?a=admin_idfeedsyndication");
options::oldMenu('Features', "CCP API Feed", KB_HOST."/?a=admin_apimod");

options::oldMenu('Maintenance', "Auditing", KB_HOST."/?a=admin_audit");
options::oldMenu('Maintenance', "Troubleshooting", KB_HOST."/?a=admin_troubleshooting");
options::oldMenu('Maintenance', "File Verification", KB_HOST."/?a=admin_verify");
options::oldMenu('Maintenance', "Upgrade", KB_HOST."/?a=admin_upgrade");
options::oldMenu('Maintenance', "Settings Report", KB_HOST."/?a=admin_status");
options::oldMenu('Kill Import/Export', "Kill Import - files", KB_HOST."/?a=admin_kill_import");
options::oldMenu('Kill Import/Export', "Kill Import - csv", KB_HOST."/?a=admin_kill_import_csv");
options::oldMenu('Kill Import/Export', "Kill Export - files", KB_HOST."/?a=admin_kill_export");
//options::oldMenu('Kill Import/Export', "Kill Export - csv", KB_HOST."/?a=admin_kill_export_search");
options::oldMenu('- Logout -', "Logout", KB_HOST."/?a=logout");

#options::oldMenu('User', 'Titles', '?a=admin_titles');

options::oldMenu('Appearance', "Top Navigation", KB_HOST."/?a=admin_navmanager");

