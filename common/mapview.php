<?php
require_once('common/includes/class.map.php');

function checkColors($context)
{
	global $view;

	$a = array('line', 'bg', 'hl', 'normal', 'capt');
	foreach ($a as $b)
	{
		if ($string = config::get('map_'.$context.'_cl_'.$b))
		{
			$tmp = explode(',', $string);
			$function = 'set'.$b.'color';
			$view->$function($tmp[0], $tmp[1], $tmp[2]);
		}
	}
}

$view = new MapView($_GET['mode'], intval($_GET['size']));
$view->setSystemID(intval($_GET['sys_id']));
switch ($_GET['mode'])
{
	case "map":
		$view->setTitle("Region");
		$view->showLines(config::get('map_map_showlines'));
		$view->paintSecurity(config::get('map_map_security'));
		checkColors('map');
		break;
	case "region":
		$view->setTitle("Constellation");
		$view->showLines(config::get('map_reg_showlines'));
		$view->paintSecurity(config::get('map_reg_security'));
		$view->setOffset(25);
		checkColors('reg');
		break;
	case "cons":
		$view->showLines(config::get('map_con_showlines'));
		$view->showSysNames(config::get('map_con_shownames'));
		$view->paintSecurity(config::get('map_con_security'));
		$view->setOffset(25);
		checkColors('con');
		break;
	default: exit;
}

$view->generate();
