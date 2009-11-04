<?php

options::cat('Appearance', 'Global Options', 'Global Look');
options::fadd('Banner', 'style_banner', 'select', array('admin_appearance', 'createSelectBanner'));
options::fadd('Style', 'style_name', 'select', array('admin_appearance', 'createSelectStyle'));
options::fadd('Theme', 'theme_name', 'select', array('admin_appearance', 'createSelectTheme'), array('admin_appearance', 'changeTheme'));

options::cat('Appearance', 'Global Options', 'Global Options');
options::fadd('Display standings', 'show_standings', 'checkbox');
options::fadd('Enable lost item values', 'item_values', 'checkbox');
//options::fadd('Use custom shipvalues', 'ship_values', 'checkbox');
options::fadd('Display a link instead of POD on Battlesummary', 'bs_podlink', 'checkbox');
options::fadd('Split up fitted items on Killmails', 'kill_splitfit', 'checkbox');
options::fadd('Use gmdate instead of date', 'date_gmtime', 'checkbox');
options::fadd('Classify kills for hours:', 'kill_classified', 'edit:size:4', '', '', '0 to disable, 1-24hrs');

options::cat('Appearance', 'Global Options', 'User Registration');
options::fadd('Show user-menu on every page', 'user_showmenu', 'checkbox');
options::fadd('Registration disabled', 'user_regdisabled', 'checkbox');
options::fadd('Registration password', 'user_regpass', 'edit');
options::fadd('Allow out-of-game registration', 'user_noigb', 'checkbox');

options::cat('Appearance', 'Front Page', 'Front Page');
options::fadd('Display combined kills and losses on Front Page', 'show_comb_home', 'checkbox');
options::fadd('Display comment count on Front Page', 'comments_count', 'checkbox');
options::fadd('Display involved count on Front Page', 'killlist_involved', 'checkbox');
options::fadd('Display alliance logos on Front Page', 'killlist_alogo', 'checkbox');
options::fadd('Show Corp: / Alliance: on Front Page', 'corpalliance-name', 'checkbox');
options::fadd('Display clock on Front Page', 'show_clock', 'checkbox');

options::cat('Appearance', 'Front Page', 'Kill Summary Tables');
options::fadd('Display Summary Table (Also works on the Monthly mod)', 'summarytable', 'checkbox');
//options::fadd('Amount in each Column', 'summarytable_rowcount', 'edit:size:2');
//options::fadd('Number of columns', 'summarytable_colcount', 'edit:size:2');
options::fadd('Display a summary line below a Summary Table', 'summarytable_summary', 'checkbox');
options::fadd('Display efficiency in the summary line', 'summarytable_efficiency', 'checkbox');
options::fadd('Amount of shown kills on front, kills and losses pages', 'killcount', 'edit:size:2');

options::cat('Appearance', 'Kill Details', 'Kill Details');
options::fadd('Display killpoints', 'kill_points', 'checkbox');
options::fadd('Display losspoints', 'loss_points', 'checkbox');
options::fadd('Display totalpoints', 'total_points', 'checkbox');
options::fadd('Include dropped value in total loss', 'kd_droptototal', 'checkbox');
options::fadd('Use lighter green for dropped items', 'kd_lgreen', 'checkbox');
options::fadd('Show Faction items tag', 'kd_ftag', 'checkbox');
options::fadd('Show Deadspace items tag', 'kd_dtag', 'checkbox');
options::fadd('Show Officer items tag', 'kd_otag', 'checkbox');
options::fadd('Show Fitting Panel', 'fp_show', 'checkbox');
options::fadd('Export EFT fittings', 'kd_EFT', 'checkbox');

options::cat('Appearance', 'Kill Details', 'Fitting Panel');
options::fadd('Panel Theme', 'fp_theme', 'select', array('admin_appearance', 'createPanelTheme'));
options::fadd('Panel Style', 'fp_style', 'select', array('admin_appearance', 'createPanelStyle'));
options::fadd('Item Highlight Style', 'fp_highstyle', 'select', array('admin_appearance', 'createHighStyle'));
options::fadd('Ammo Highlight Style', 'fp_ammostyle', 'select', array('admin_appearance', 'createAmmoStyle'));
options::fadd('Highlight Tech II items', 'fp_ttag', 'checkbox');
options::fadd('Highlight Faction items', 'fp_ftag', 'checkbox');
options::fadd('Highlight Deadspace items', 'fp_dtag', 'checkbox');
options::fadd('Highlight Officer items', 'fp_otag', 'checkbox');

class admin_appearance
{
    function createPanelTheme()
    {
	$sfp_themes =array("ArmyGreen" ,
		"CoolGray" ,
		"DarkOpaque" ,
		"Desert" ,
		"Revelations" ,
		"RevelationsII" ,
		"Silver" ,
		"Stealth" ,
		"SteelGray" ,
		"Trinity" ,
		"Black" ,
		"Blue" ,
		"Gold" ,
		"Green" ,
		"LightBlue" ,
		"Red" ,
		"Yellow" ,
		"Vidar" ,
		"Demonic" );
	$option = array();
	$selected = config::get('fp_theme');
	foreach ($sfp_themes as $theme)
	{
	    if ($theme == $selected)
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
	$sfp_styles =array("Windowed" ,
		"OldWindow" ,
		"Border" ,
		"Faded" );
	$option = array();
	$selected = config::get('fp_style');
	foreach ($sfp_styles as $style)
	{
	    if ($style == $selected)
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
	$sfp_highstyles =array("ring" ,
		"square" ,
		"round" ,
		"backglowing" );
	$option = array();
	$selected = config::get('fp_highstyle');
	foreach ($sfp_highstyles as $style)
	{
	    if ($style == $selected)
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
	$sfp_ammostyles =array("solid" ,
		"transparent" );
	$option = array();
	$selected = config::get('fp_ammostyle');
	foreach ($sfp_ammostyles as $style)
	{
	    if ($style == $selected)
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

    function createSelectBanner()
    {
        $options = array();
        $dir = "banner/";
        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                while (($file = readdir($dh)) !== false)
                {
                    $file = substr($file, 0);
                    if (!is_dir($dir.$file))
                    {
                        if (config::get('style_banner') == $file)
                        {
                            $state = 1;
                        }
                        else
                        {
                            $state = 0;
                        }

                        $options[] = array('value' => $file, 'descr' => $file, 'state' => $state);
                    }
                }
                closedir($dh);
            }
        }
        return $options;
    }

    function createSelectStyle()
    {
        $dir = "themes/".config::get('theme_name')."/";
        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if (!is_dir($dir.$file))
                    {
                        if (substr($file, -4) != ".css")
                        {
                            continue;
                        }
                        if (config::get('style_name').'.css' == $file)
                        {
                            $state = 1;
                        }
                        else
                        {
                            $state = 0;
                        }

                        $options[] = array('value' => substr($file,0,-4), 'descr' => substr($file,0,-4), 'state' => $state);
                    }
                }
                closedir($dh);
            }
        }
        return $options;
    }
	function createSelectTheme()
    {
        $dir = "themes/";
        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if (is_dir($dir.$file))
                    {
                        if ($file == "." || $file == ".." || $file == ".svn")
                        {
                            continue;
                        }
                        if (config::get('theme_name') == $file)
                        {
                            $state = 1;
                        }
                        else
                        {
                            $state = 0;
                        }

                        $options[] = array('value' => $file, 'descr' => $file, 'state' => $state);
                    }
                }
                closedir($dh);
            }
        }
        return $options;
    }
	function changeTheme()
	{
		if(!isset($_REQUEST['option_theme_name'])) return;
		if(!file_exists("themes/".config::get('theme_name')."/".config::get('style_name').".css"))
			config::set('style_name', config::get('theme_name'));
		admin_appearance::removeOld(0, KB_CACHEDIR.'/templates_c', false);
		header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
		die;
	}
	function removeOld($hours, $dir, $recurse = false)
	{
		if(!session::isAdmin()) return false;
		if(strpos($dir, '.') !== false) return false;
		if(!is_dir($dir)) return false;
		if(substr($dir,-1) != '/') $dir = $dir.'/';
		$seconds = $hours*60*60;
		$files = scandir($dir);

		foreach ($files as $num => $fname)
		{
			if (file_exists("{$dir}{$fname}") && !is_dir("{$dir}{$fname}") && substr($fname,0,1) != "." && ((time() - filemtime("{$dir}{$fname}")) > $seconds))
			{
				$mod_time = filemtime("{$dir}{$fname}");
				if (unlink("{$dir}{$fname}")) $del = $del + 1;
			}
			if ($recurse && file_exists("{$dir}{$fname}") && is_dir("{$dir}{$fname}")
				 && substr($fname,0,1) != "." && $fname !== ".." )
			{
				$del = $del + admin_acache::remove_old($hours, $dir.$fname."/");
			}
		}
		return $del;
	}
}
?>