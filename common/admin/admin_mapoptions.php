<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Mapoptions');

if ($_POST['submit'])
{
	config::checkCheckbox('map_map_showlines');
	config::checkCheckbox('map_reg_showlines');
	config::checkCheckbox('map_con_showlines');
	config::checkCheckbox('map_con_shownames');
	config::checkCheckbox('map_map_security');
	config::checkCheckbox('map_reg_security');
	config::checkCheckbox('map_con_security');

	foreach ($_POST as $key => $value)
	{
		if (strpos($key, '_cl_'))
		{
			if ($value)
			{
				if (substr($value,0,1) == '#')
				{
					$value = 'x'.substr($value, 1, 2).',x'.substr($value, 3, 2).',x'.substr($value, 5, 2);
				}
				$value = preg_replace('/[^a-fA-F0-9,x]/', '', $value);
				$tmp = explode(',', $value);
				if (count($tmp) != 3)
				{
					continue;
				}
				$val = array();
				for ($i = 0; $i < 3; $i++)
				{
					if (preg_match('/[a-fA-Fx]/', $tmp[$i]))
					{
						$tmp[$i] = str_replace('x', '', $tmp[$i]);
						$tmp[$i] = base_convert($tmp[$i], 16, 10);
					}
					$val[$i] = min(max($tmp[$i], 0), 255);
				}
				$string = implode(',', $val);
				config::set($key, $string);
			}
			else
			{
				config::del($key);
			}
		}
	}

	// on submit delete all region cache files
	if(is_dir(KB_CACHEDIR.'/img/map/'.KB_SITE))
	{
		$dir = opendir(KB_CACHEDIR.'/img/map/'.KB_SITE);
		while ($file = readdir($dir))
		{
			if (strpos($file, '.png'))
			{
				@unlink(KB_CACHEDIR.'/img/map/'.KB_SITE.'/'.$file);
			}
		}
	}
}

$options = array();
$options[0]['name'] = 'Region Options';
$options[0]['option'][] = array('descr' => 'Show Lines', 'name' => 'map_map_showlines');
$options[0]['option'][] = array('descr' => 'Paint Security', 'name' => 'map_map_security');
$options[0]['color'][] = array('descr' => 'Linecolor', 'name' => 'map_map_cl_line');
$options[0]['color'][] = array('descr' => 'Captioncolor', 'name' => 'map_map_cl_capt');
$options[0]['color'][] = array('descr' => 'Backgroundcolor', 'name' => 'map_map_cl_bg');
$options[0]['color'][] = array('descr' => 'Normalcolor', 'name' => 'map_map_cl_normal');
$options[0]['color'][] = array('descr' => 'Highlightcolor', 'name' => 'map_map_cl_hl');

$options[1]['name'] = 'Constellation Options';
$options[1]['option'][] = array('descr' => 'Show Lines', 'name' => 'map_reg_showlines');
$options[1]['option'][] = array('descr' => 'Paint Security', 'name' => 'map_reg_security');
$options[1]['color'][] = array('descr' => 'Linecolor', 'name' => 'map_reg_cl_line');
$options[1]['color'][] = array('descr' => 'Captioncolor', 'name' => 'map_reg_cl_capt');
$options[1]['color'][] = array('descr' => 'Backgroundcolor', 'name' => 'map_reg_cl_bg');
$options[1]['color'][] = array('descr' => 'Normalcolor', 'name' => 'map_reg_cl_normal');
$options[1]['color'][] = array('descr' => 'Highlightcolor', 'name' => 'map_reg_cl_hl');

$options[2]['name'] = 'System Options';
$options[2]['option'][] = array('descr' => 'Show Lines', 'name' => 'map_con_showlines');
$options[2]['option'][] = array('descr' => 'Show Sytem Names', 'name' => 'map_con_shownames');
$options[2]['option'][] = array('descr' => 'Paint Security', 'name' => 'map_con_security');
$options[2]['color'][] = array('descr' => 'Linecolor', 'name' => 'map_con_cl_line');
$options[2]['color'][] = array('descr' => 'Captioncolor', 'name' => 'map_con_cl_capt');
$options[2]['color'][] = array('descr' => 'Backgroundcolor', 'name' => 'map_con_cl_bg');
$options[2]['color'][] = array('descr' => 'Normalcolor', 'name' => 'map_con_cl_normal');
$options[2]['color'][] = array('descr' => 'Highlightcolor', 'name' => 'map_con_cl_hl');

$smarty->assignByRef('config', $config);
$smarty->assignByRef('options', $options);
$html = $smarty->fetch(get_tpl('admin_mapoptions'));

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
