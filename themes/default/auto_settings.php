<?php
/**
 * Theme settings automatically loaded from admin_menu.php
 * 
 * @package EDK
 */

if (!class_exists('options'))
	exit('This killboard is not supported (options package missing)!');

options::cat('Appearance', 'Global Options', 'Global Look');
options::fadd('Background', 'style_background', 'select', array('admin_appearance_default', 'createSelectBackground'), array('admin_appearance_default', 'changeBackground'));
options::fadd('JQuery UI Theme', 'jqtheme_name', 'select', array('admin_appearance_default', 'createSelectJQTheme'), array('admin_appearance_default', 'changeJQTheme'));
options::fadd('Background Color', 'style_background_color', 'edit' );

class admin_appearance_default extends admin_appearance {
	function createSelectJQTheme() {
		return self::createSelectTheme("themes/default/jquerythemes", 'jqtheme_name');
	}

	function changeJQTheme()
	{
		global $themename;
		if(options::getPrevious('jqtheme_name') == $_POST['option_jqtheme_name'])
			return;

		$jqthemename = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['option_jqtheme_name']);
		if(!is_dir("themes/default/jquerythemes/$jqtheme_name"))
			$jqthemename = 'base';

		$_POST['option_jqtheme_name'] = $jqthemename;
		config::set('jqtheme_name', $jqthemename);
	}

	/* Create the selection options for available banners
	 * @return stringHTML for the background selection dropdown list.
	 */
	function createSelectBackground()
	{
		$options = array();

		if(config::get('style_background') == "0")
			$state = 1;
		else
			$state = 0;
		$options[] = array('value' => "0", 'descr' => "No background", 'state' => $state);

		$dir = "background/";
		if(is_dir($dir))
		{
			if($dh = scandir($dir))
			{
				foreach($dh as $file)
				{
					$file = substr($file, 0);
					if(!is_dir($dir.$file))
					{
						if(config::get('style_background') == $file) $state = 1;
						else $state = 0;

						$options[] = array('value' => $file, 'descr' => $file, 'state' => $state);
					}
				}
			}
		}
		return $options;
	}

	function changeBackground()
	{
		global $smarty;
		if(options::getPrevious('style_background') == $_POST['option_style_background'])
			return;
		if($_POST['option_style_background'] == 0) return;

		$dimensions = getimagesize('background/'.$_POST['option_style_background']);
		if(!$dimensions) $dimensions = array(0, 0);

		//config::set('style_background_x', $dimensions[0]);
		//config::set('style_background_y', $dimensions[1]);
		//$smarty->assign('background_x', $dimensions[0]);
		//$smarty->assign('background_y', $dimensions[1]);
	}
}
