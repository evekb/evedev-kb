<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


options::cat('Appearance', 'Global Options', 'Global Look');
options::fadd('Banner', 'style_banner', 'select', array('admin_appearance', 'createSelectBanner'), array('admin_appearance', 'changeBanner'));
options::fadd('Theme', 'theme_name', 'select', array('admin_appearance', 'createSelectTheme'), array('admin_appearance', 'changeTheme'));
options::fadd('Style', 'style_name', 'select', array('admin_appearance', 'createSelectStyle'), array('admin_appearance', 'changeStyle'));
options::fadd('Language', 'cfg_language', 'select', array('admin_appearance', 'createLanguage'));

options::cat('Appearance', 'Global Options', 'Global Options');
options::fadd('Display standings', 'show_standings', 'checkbox');
options::fadd('Enable lost item values', 'item_values', 'checkbox');
options::fadd('Display a link instead of POD on Battlesummary', 'bs_podlink', 'checkbox');
options::fadd('Include Capsules, Shuttles and Noobships in kills', 'podnoobs', 'checkbox');
options::fadd('Classify kills for hours:', 'kill_classified', 'edit:size:4', '', '', '0 to disable, 1-24hrs');

options::cat('Appearance', 'Global Options', 'User Registration');
options::fadd('Show user-menu on every page', 'user_showmenu', 'checkbox');
options::fadd('Registration disabled', 'user_regdisabled', 'checkbox');
options::fadd('Registration password', 'user_regpass', 'edit');
options::fadd('Allow out-of-game registration', 'user_noigb', 'checkbox');

options::cat('Appearance', 'Front Page', 'Front Page');
options::fadd('Combine kills and losses', 'show_comb_home', 'checkbox');
options::fadd('Fill home page', 'cfg_fillhome', 'checkbox', '', '', 'Include kills from previous week/months to fill home page');
options::fadd('Display region names', 'killlist_regionnames', 'checkbox');
options::fadd('Display comment count', 'comments_count', 'checkbox');
options::fadd('Display involved count', 'killlist_involved', 'checkbox');
options::fadd('Display clock', 'show_clock', 'checkbox');
options::fadd('Display Monthly stats', 'show_monthly', 'checkbox', '', '', 'Default is weekly');

options::cat('Appearance', 'Front Page', 'Kill Summary Tables');
options::fadd('Display Summary Table', 'summarytable', 'checkbox');
options::fadd('Display a summary line below a Summary Table', 'summarytable_summary', 'checkbox');
options::fadd('Display efficiency in the summary line', 'summarytable_efficiency', 'checkbox');

options::cat('Appearance', 'Front Page', 'Kill Lists');
options::fadd('Amount of kills listed', 'killcount', 'edit:size:2');

options::cat('Appearance', 'Kill Details', 'Kill Details');
options::fadd('Display killpoints', 'kill_points', 'checkbox');
options::fadd('Display losspoints', 'loss_points', 'checkbox');
options::fadd('Display totalpoints', 'total_points', 'checkbox');
options::fadd('Show Total ISK Loss, Damage at top', 'kd_showiskd', 'checkbox');
options::fadd('Show Top Damage Dealer/Final Blow Boxes', 'kd_showbox', 'checkbox');
options::fadd('Show involved parties summary', 'kd_showext', 'checkbox');
options::fadd('Include dropped value in total loss', 'kd_droptototal', 'checkbox');

//options::fadd('Show T2 items tag', 'kd_ttag', 'checkbox');
//options::fadd('Show Faction items tag', 'kd_ftag', 'checkbox');
//options::fadd('Show Deadspace items tag', 'kd_dtag', 'checkbox');
//options::fadd('Show Officer items tag', 'kd_otag', 'checkbox');
options::fadd('Show Fitting Panel', 'fp_show', 'checkbox');
options::fadd('Show Fitting Exports', 'kd_EFT', 'checkbox');
options::fadd('Limit involved parties', 'kd_involvedlimit', 'edit:size:4', '', '', 'Leave blank for no limit.');

options::cat('Appearance', 'Kill Details', 'Fitting Panel');
options::fadd('Panel Theme', 'fp_theme', 'select', array('admin_appearance', 'createPanelTheme'));
options::fadd('Panel Style', 'fp_style', 'select', array('admin_appearance', 'createPanelStyle'));
options::fadd('Item Highlight Style', 'fp_highstyle', 'select', array('admin_appearance', 'createHighStyle'));
options::fadd('Ammo Highlight Style', 'fp_ammostyle', 'select', array('admin_appearance', 'createAmmoStyle'));
options::fadd('Show Ammo, charges, etc', 'fp_showammo', 'checkbox');
//options::fadd('Highlight Tech II items', 'fp_ttag', 'checkbox');
//options::fadd('Highlight Faction items', 'fp_ftag', 'checkbox');
//options::fadd('Highlight Deadspace items', 'fp_dtag', 'checkbox');
//options::fadd('Highlight Officer items', 'fp_otag', 'checkbox');

class admin_appearance
{
	function createPanelTheme()
	{
		$sfp_themes = array("tyrannis",
			"tyrannis_blue",
			"tyrannis_darkred",
			"tyrannis_default",
			"tyrannis_revelations");
		$option = array();
		$selected = config::get('fp_theme');
		foreach($sfp_themes as $theme)
		{
			if($theme == $selected)
			{
				$state = 1;
			}
			else
			{
				$state = 0;
			}
			$options[] = array('value' => $theme, 'descr' => $theme, 'state' => $state);
		}
		return $options;
	}

	function createPanelStyle()
	{
		$sfp_styles = array("Windowed",
			"OldWindow",
			"Border",
			"Faded");
		$option = array();
		$selected = config::get('fp_style');
		foreach($sfp_styles as $style)
		{
			if($style == $selected)
			{
				$state = 1;
			}
			else
			{
				$state = 0;
			}
			$options[] = array('value' => $style, 'descr' => $style, 'state' => $state);
		}
		return $options;
	}

	function createHighStyle()
	{
		$sfp_highstyles = array("ring",
			"square",
			"round",
			"backglowing",
			"tag",
			"none");
		$option = array();
		$selected = config::get('fp_highstyle');
		foreach($sfp_highstyles as $style)
		{
			if($style == $selected)
			{
				$state = 1;
			}
			else
			{
				$state = 0;
			}
			$options[] = array('value' => $style, 'descr' => $style, 'state' => $state);
		}
		return $options;
	}

	function createAmmoStyle()
	{
		$sfp_ammostyles = array("solid",
			"transparent",
			"none");
		$option = array();
		$selected = config::get('fp_ammostyle');
		foreach($sfp_ammostyles as $style)
		{
			if($style == $selected)
			{
				$state = 1;
			}
			else
			{
				$state = 0;
			}
			$options[] = array('value' => $style, 'descr' => $style, 'state' => $state);
		}
		return $options;
	}

	/* Create the selection options for available banners
	 * @return stringHTML for the banner selection dropdown list.
	 */
	function createSelectBanner()
	{
		$options = array();

		if(config::get('style_banner') == "0") $state = 1;
		else $state = 0;
		$options[] = array('value' => "0", 'descr' => "No banner", 'state' => $state);

		$dir = "banner/";
		if(is_dir($dir))
		{
			if($dh = scandir($dir))
			{
				foreach($dh as $file)
				{
					$file = substr($file, 0);
					if(!is_dir($dir.$file))
					{
						if(config::get('style_banner') == $file) $state = 1;
						else $state = 0;

						$options[] = array('value' => $file, 'descr' => $file, 'state' => $state);
					}
				}
			}
		}
		return $options;
	}

	/** Create the selection options for available styles in the current theme.
	 *
	 * @return string HTML for the style selection dropdown list.
	 */
	function createSelectStyle()
	{
		$options = array();
		$dir = "themes/".config::get('theme_name')."/";

		if(is_dir($dir))
		{
			if($dh = scandir($dir))
			{
				foreach($dh as $file)
				{
					if(!is_dir($dir.$file))
					{
						if(substr($file, -4) != ".css") continue;

						if(config::get('style_name').'.css' == $file) $state = 1;
						else $state = 0;

						$options[] = array('value' => substr($file, 0, -4), 'descr' => substr($file, 0, -4), 'state' => $state);
					}
				}
			}
		}
		return $options;
	}

	/** Create the selection options for available themes.
	 *
	 * @return string HTML for the theme selection dropdown list.
	 */
	function createSelectTheme()
	{
		$options = array();
		$dir = "themes/";

		if(is_dir($dir))
		{
			if($dh = scandir($dir))
			{
				foreach($dh as $file)
				{
					if(is_dir($dir.$file))
					{
						if($file == "." || $file == ".." || $file == ".svn") continue;
						if(config::get('theme_name') == $file) $state = 1;
						else $state = 0;

						$options[] = array('value' => $file, 'descr' => $file, 'state' => $state);
					}
				}
			}
		}
		return $options;
	}

	/**
	 * Checks if theme has changed and updates page before display.
	 */
	function changeTheme()
	{
		global $themename;
		if(options::getPrevious('theme_name') == $_POST['option_theme_name']) return;

		$themename = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['option_theme_name']);
		if(!is_dir("themes/$themename")) $themename = 'default';

		$_POST['option_theme_name'] = $themename;
		config::set('theme_name', $themename);

		global $smarty;
		$smarty->assign('theme_url', config::get('cfg_kbhost').'/themes/'.$themename);
		$smarty->template_dir = './themes/'.$themename.'/templates';
		if(!file_exists(KB_CACHEDIR.'/templates_c/'.$themename.'/'))
				mkdir(KB_CACHEDIR.'/templates_c/'.$themename.'/', 0755, true);
		$smarty->compile_dir = KB_CACHEDIR.'/templates_c/'.$themename.'/';
		CacheHandler::removeByAge('templates_c/'.$themename, 0, false);
	}

	/**
	 * Updates style before page is displayed.
	 */
	function changeStyle()
	{
		global $smarty;
		if(options::getPrevious('theme_name') != $_POST['option_theme_name'])
		{
			$themename = preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['option_theme_name']);
			if(!is_dir("themes/$themename")) $themename = 'default';

			$arr = reset(self::createSelectStyle());

			config::set('style_name', $arr['value']);
			$_POST['option_style_name'] = $arr['value'];

			$smarty->assign('style', $arr['value']);
		}
		elseif(options::getPrevious('style_name') != $_POST['option_style_name'])
		{
			$smarty->assign('style', preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['option_style_name']));
		}
	}

	/**
	 * Checks if banner has changed, updates page before display and resets banner size.
	 *
	 * If the banner is changed the stored size is updated and used to display
	 *  the banner image. Smarty variables are updated so display is immediate.
	 */
	function changeBanner()
	{
		global $smarty;
		if(options::getPrevious('style_banner') == $_POST['option_style_banner'])
				return;
		if($_POST['option_style_banner'] == 0) return;

		$dimensions = getimagesize('banner/'.$_POST['option_style_banner']);
		if(!$dimensions) $dimensions = array(0, 0);

		config::set('style_banner_x', $dimensions[0]);
		config::set('style_banner_y', $dimensions[1]);

		$smarty->assign('banner_x', $dimensions[0]);
		$smarty->assign('banner_y', $dimensions[1]);
	}
	public static function createLanguage()
	{
		$options = array();
		$dir = scandir('common/language');
		foreach($dir as $file) {
			if (substr($file, 0, 1) == '.'
					|| substr($file, -4) != '.php') {
				continue;
			}
			if (config::get('cfg_language') == substr($file, 0, -4)) {
				$state = 1;
			} else {
				$state = 0;
			}
			$options[] = array('value' => substr($file, 0, -4),
				'descr' => substr($file, 0, -4), 'state' => $state);
		}
		return $options;
	}
}
