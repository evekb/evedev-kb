<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
require_once('common/admin/admin_menu.php');

$page = new Page('Administration - Mods');
$page->setAdmin();

if (isset($_POST['set_mods']) && $_POST['set_mods']) {
	foreach ($_POST as $key => $val) {
		if (substr($key, 0, 4) == "mod_" && $val == "on") {
			if (substr($key, 4, strlen($key) - 4) != 'item_values') {
				$activemods .= substr($key, 4, strlen($key) - 4) . ",";
			}
		}
	}
	$activemods = substr($activemods, 0, strlen($activemods) - 1);
	config::set("mods_active", $activemods);
}
$activemods = explode(",", config::get("mods_active"));

$rows = array();
if ($handle = opendir('mods')) {
	$modlist = array();
	while ($file = readdir($handle)) {
		if (is_dir("mods/$file") && $file != ".." & $file != "." & $file != ".svn") {
			$rows[$file] = array(
				'name' => $file,
				'url' => edkURI::page("settings_$file"),
				'checked' => in_array($file, $activemods),
				'settings' => file_exists("mods/$file/settings.php"));
		}
	}
	ksort($rows);
	closedir($handle);
}
$smarty->assign('rows', $rows);
$page->setContent($smarty->fetch(get_tpl('admin_mods')));

$page->addContext($menubox->generate());
$page->generate();
