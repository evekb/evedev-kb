<?php
require_once('common/includes/class.options.php');

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

// overload the box object to force every admin page to use the new options menu
class Box2 extends Box
{
    function generate()
    {
        return options::genAdminMenu();
    }
}

$menubox = new Box2();

options::oldMenu('Features', "Contracts", "?a=admin_cc&amp;op=view&amp;type=contract");
options::oldMenu('Features', "Campaigns", "?a=admin_cc&amp;op=view&amp;type=campaign");
options::oldMenu('Features', "Standings", "?a=admin_standings");

options::oldMenu('Appearance', "Map Options", "?a=admin_mapoptions");

options::oldMenu('Advanced', "Ship Values", "?a=admin_shp_val");
options::oldMenu('Advanced', "Item Values", "?a=admin_value_editor");
options::oldMenu('Advanced', "Post Permissions", "?a=admin_postperm");

options::oldMenu('Features', "Modules", "?a=admin_mods");

options::oldMenu('Features', "Feed Syndication", "?a=admin_feedsyndication");
options::oldMenu('Features', "API Mod", "?a=admin_apimod");

options::oldMenu('Maintenance', "Auditing", "?a=admin_audit");
options::oldMenu('Maintenance', "Troubleshooting", "?a=admin_troubleshooting");
options::oldMenu('Maintenance', "Settings Report", "?a=admin_status");
options::oldMenu('Kill Import/Export', "Kill Import - files", "?a=admin_kill_import");
options::oldMenu('Kill Import/Export', "Kill Import - csv", "?a=admin_kill_import_csv");
options::oldMenu('Kill Import/Export', "Kill Export - files", "?a=admin_kill_export");
options::oldMenu('Kill Import/Export', "Kill Export - csv", "?a=admin_kill_export_search");
options::oldMenu('- Logout -', "Logout", "?a=logout");

#options::oldMenu('User', 'Titles', '?a=admin_titles');

options::oldMenu('Appearance', "Top Navigation", "?a=admin_navmanager");
?>
