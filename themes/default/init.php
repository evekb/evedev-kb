<?php
/**
 * Theme initialization called from index.php.
 * 
 * @package EDK
 */

$themeInfo = array("name" => "default", "info"=>"The default theme for EDK.");
$themeVersion = KB_VERSION;

$jqtheme_name = config::get('jqtheme_name');

if(!is_dir("themes/default/jquerythemes/$jqtheme_name")) {
	$jqtheme_name = 'base';
}

$smarty->assign('jqtheme_name', $jqtheme_name);
