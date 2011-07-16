<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

@set_time_limit(0);
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Killmail Exporter');

if (!$_POST['dir'])
{
	$dir = KB_CACHEDIR.'/kill_export';
}
if (!$_POST['ext'])
{
	$ext = '.txt';
}
else
{
	$ext = $_POST['ext'];
}
if ($_POST['submit'] == 'Reset')
{
	unset($_SESSION['admin_kill_export']);
	unset($_POST);
}
elseif ($_GET['sub'] == 'do')
{
	unset($_SESSION['admin_kill_export']['select']);
	$_SESSION['admin_kill_export']['do'] = 1;
}

$html .= "<form id=\"options\" name=\"options\" method=\"post\" action=\"?a=admin_kill_export\">";
$html .= '<input type="hidden" value="" name=""/>';

if ($_POST)
{
	$dir = $_POST['dir'];
	if (!$dir && $_SESSION['admin_kill_export']['dir'])
	{
		$dir = $_SESSION['admin_kill_export']['dir'];
		$ext = $_SESSION['admin_kill_export']['ext'];
	}
	if (!strstr(stripslashes($dir), stripslashes(str_replace('\\','/',getcwd()))))
	{
		$dir = str_replace('\\','/',getcwd()).'/'.$dir;
	}
	$dir = str_replace('//','/',$dir);

	if (substr($dir, -1, 1) != '/')
	{
		$dir .= '/';
	}
	if (is_dir($dir))
	{
		if (is_writeable($dir))
		{
			$html .= "'$dir' is valid and writeable<br/>";
			$_SESSION['admin_kill_export']['select'] = 1;
			$_SESSION['admin_kill_export']['dir'] = $dir;
			$_SESSION['admin_kill_export']['ext'] = $ext;
		}
	}
	else
	{
		$html .= "'$dir' does not exist, trying to create it...";
		if (mkdir($dir))
		{
			$html .= 'successful<br/>';
			$_SESSION['admin_kill_export']['select'] = 1;
			$_SESSION['admin_kill_export']['dir'] = $dir;
			$_SESSION['admin_kill_export']['ext'] = $ext;
			chmod($dir, 0777);
		}
		else
		{
			$html .= 'failed<br/>';
		}
	}
	if (!isset($_SESSION['admin_kill_export']['to_export']))
	{
		$comma = false;
		$str = '';
		if(config::get('cfg_allianceid'))
		{
			$str = 'a'.implode(',a', config::get('cfg_allianceid'));
			$comma = true;
		}
		if (config::get('cfg_corpid'))
		{
			if($comma) $str .= ',';
			else $comma = true;
			$str .= 'c'.implode(',c', config::get('cfg_corpid'));
		}
		if (config::get('cfg_pilotid'))
		{
			if($comma) $str .= ',';
			$str .= 'p'.implode(',p', config::get('cfg_pilotid'));
		}
		$_SESSION['admin_kill_export']['to_export'] = $str;

	}
}
elseif (!isset($_SESSION['admin_kill_export']['do']) || !isset($_SESSION['admin_kill_export']['select']))
{
	$html .= "<div class=block-header2>Select a folder to export the Killmails to</div>";
	$html .= "<table class=kb-subtable>";
	$html .= "<tr><td width=120><b>Directory:</b></td><td><input type=text name=dir id=dir size=60 maxlength=80 value=\"".$dir."\"></td></tr>";
	$html .= "<tr><td width=120><b>Extension:</b></td><td><input type=text name=ext id=ext size=3 maxlength=10 value=\"".$ext."\"></td></tr>";
	$html .= "<tr><td width=120><b>Attention:</b></td><td>For security reasons only directories below the main EDK directory will be used.</td></tr>";
	$html .= "<tr><td width=120></td><td><input type=submit name=submit value=\"Check\"></td></tr>";
	$html .= "</table>";
}
$html .= "</form>";

if (isset($_SESSION['admin_kill_export']['select']))
{
	if ($_POST['searchphrase'] != "" && strlen($_POST['searchphrase']) >= 3)
	{
		switch ($_POST['searchtype'])
		{
			case "pilot":
				$sql = "select plt.plt_id, plt.plt_name, crp.crp_name
                        from kb3_pilots plt, kb3_corps crp
                        where lower( plt.plt_name ) like lower( '%".slashfix($_POST['searchphrase'])."%' )
                        and plt.plt_crp_id = crp.crp_id
                        order by plt.plt_name";
				break;
			case "corp":
				$sql = "select crp.crp_id, crp.crp_name, ali.all_name
                        from kb3_corps crp, kb3_alliances ali
                        where lower( crp.crp_name ) like lower( '%".slashfix($_POST['searchphrase'])."%' )
                        and crp.crp_all_id = ali.all_id
                        order by crp.crp_name";
				break;
			case "alliance":
				$sql = "select ali.all_id, ali.all_name
                        from kb3_alliances ali
                        where lower( ali.all_name ) like lower( '%".slashfix($_POST['searchphrase'])."%' )
                        order by ali.all_name";
				break;
		}

		$qry = DBFactory::getDBQuery();
		;
		$qry->execute($sql);

		while ($row = $qry->getRow())
		{
			switch ($_POST['searchtype'])
			{
				case 'pilot':
					$link = KB_HOST.'/?a=admin_kill_export&add=p'.$row['plt_id'];
					$descr = 'Pilot '.$row['plt_name'].' from '.$row['crp_name'];
					break;
				case 'corp':
					$link = "?a=admin_kill_export&add=c".$row['crp_id'];
					$descr = 'Corp '.$row['crp_name'].', member of '.$row['all_name'];
					break;
				case 'alliance':
					$link = KB_HOST.'/?a=admin_kill_export&add=a'.$row['all_id'];
					$descr = 'Alliance '.$row['all_name'];
					break;
			}
			$results[] = array('descr' => $descr, 'link' => $link);
		}
		$smarty->assignByRef('results', $results);
		$smarty->assign('search', true);
	}

	if (!$string = $_SESSION['admin_kill_export']['to_export'])
	{
		$string = '';
	}
	$tmp = explode(',', $string);
	$permissions = array('a' => array(), 'c' => array(), 'p' => array());
	foreach ($tmp as $item)
	{
		if (!$item)
		{
			continue;
		}
		$typ = substr($item, 0, 1);
		$id = substr($item, 1);
		$permissions[$typ][$id] = $id;
	}

	if ($_GET['add'])
	{
		$typ = substr($_GET['add'], 0, 1);
		$id = intval(substr($_GET['add'], 1));
		$permissions[$typ][$id] = $id;
		$configstr = '';
		foreach ($permissions as $typ => $id_array)
		{
			foreach ($id_array as $id)
			{
				$conf[] = $typ.$id;
			}
		}
		$_SESSION['admin_kill_export']['to_export'] = implode(',', $conf);
	}

	if ($_GET['del'])
	{
		$typ = substr($_GET['del'], 0, 1);
		$id = intval(substr($_GET['del'], 1));
		unset($permissions[$typ][$id]);
		$conf = array();
		foreach ($permissions as $typ => $id_array)
		{
			foreach ($id_array as $id)
			{
				$conf[] = $typ.$id;
			}
		}
		$_SESSION['admin_kill_export']['to_export'] = implode(',', $conf);
	}

	asort($permissions['a']);
	asort($permissions['c']);
	asort($permissions['p']);

	$permt = array();
	foreach ($permissions as $typ => $ids)
	{
		foreach ($ids as $id)
		{
			if ($typ == 'a')
			{
				$alliance = new Alliance($id);
				$text = $alliance->getName();
				$link = KB_HOST.'/?a=admin_kill_export&del='.$typ.$id;
				$permt[$typ][] = array('text' => $text, 'link' => $link);
			}
			if ($typ == 'p')
			{
				$pilot = new Pilot($id);
				$text = $pilot->getName();
				$link = KB_HOST.'/?a=admin_kill_export&del='.$typ.$id;
				$permt[$typ][] = array('text' => $text, 'link' => $link);
			}
			if ($typ == 'c')
			{
				$corp = new Corporation($id);
				$text = $corp->getName();
				$link = KB_HOST.'/?a=admin_kill_export&del='.$typ.$id;
				$permt[$typ][] = array('text' => $text, 'link' => $link);
			}
		}
	}
	$perm = array();
	if ($permt['a'])
	{
		$perm[] = array('name' => 'Alliances', 'list' => $permt['a']);
	}
	if ($permt['p'])
	{
		$perm[] = array('name' => 'Pilots', 'list' => $permt['p']);
	}
	if ($permt['c'])
	{
		$perm[] = array('name' => 'Corporations', 'list' => $permt['c']);
	}

	$smarty->assignByRef('permissions', $perm);
	$html = $smarty->fetch(get_tpl('admin_export'));
}

if (isset($_SESSION['admin_kill_export']['do']))
{
	if ($string = $_SESSION['admin_kill_export']['to_export'])
	{
		$klist = new KillList();
		$llist = new KillList();

		$tmp = explode(',', $string);
		foreach ($tmp as $item)
		{
			if (!$item)
			{
				continue;
			}
			$typ = substr($item, 0, 1);
			$id = substr($item, 1);
			if ($typ == 'a')
			{
				$klist->addInvolvedAlliance(new Alliance($id));
				$llist->addVictimAlliance(new Alliance($id));
			}
			elseif ($typ == 'c')
			{
				$klist->addInvolvedCorp(new Corporation($id));
				$llist->addVictimCorp(new Corporation($id));
			}
			elseif ($typ == 'p')
			{
				$klist->addInvolvedPilot(new Pilot($id));
				$llist->addVictimPilot(new Pilot($id));
			}
		}

		$kills = array();
		while ($kill = $klist->getKill())
		{
			$kills[$kill->getID()] = $kill->getTimestamp();
		}
		while ($kill = $llist->getKill())
		{
			$kills[$kill->getID()] = $kill->getTimestamp();
		}

		asort($kills);

		$cnt = 0;
		foreach ($kills as $id => $timestamp)
		{
			$kill = new Kill($id);
			$cnt++;
			$file = $_SESSION['admin_kill_export']['dir'].$cnt.$_SESSION['admin_kill_export']['ext'];
			$fp = fopen($file, 'w');
			fwrite($fp, $kill->getRawMail());
			fclose($fp);
		}
		$html .= $cnt.' mails exported<br/>';
		$html .= '<a href="'.KB_HOST.'/?a=admin_kill_export">Ok</a>';
		unset($_SESSION['admin_kill_export']);
	}
	else
	{
		// nothing to export, retry
		unset($_SESSION['admin_kill_export']['do']);
		$_SESSION['admin_kill_export']['select'] = 1;
		header('Location: '.KB_HOST.'/?a=admin_kill_export');
	}
}
$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
