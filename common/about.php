<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class pAbout extends pageAssembly
{
	function __construct()
	{
		parent::__construct();
		
		$this->queue("start");
		$this->queue("top");
		$this->queue("developers");
		$this->queue("stats");
		$this->queue("theme");
		$this->queue("mods");
		$this->queue("bottom");
	}
	
	function start()
	{
		$this->page = new Page("About");
	}
	
	function developers()
	{
		global $smarty;
		// Current active developers
		$currentDeveloper = array(
			'Hon Kovell', // Various stuff (EDK2-3)
			'mastergamer', // Different various stuff
			'FriedRoadKill', // Parser, db updates and image packs
		);

		// Inactive developers
		$developer = array(
			'exi', // Various stuff (EDK <2)
			'Beansman',
			'Ralle030583',
			'Captain Thunk' // API mod
		);

		// Other contributors
		$contributor = array(
			'Karbowiak',
			'JaredC01',
			'liquidism',
			'Mitchman',
			'Coni',
			'bunjiboys',
			'EDG',
		);
		sort($developer);
		sort($contributor);

		$smarty->assignByRef('current_developer', $currentDeveloper);
		$smarty->assignByRef('developer', $developer);
		$smarty->assign('contributor', $contributor);
		if(SVN_REV != "") $svn_rev = " rev ".SVN_REV;
		else $svn_rev = "";
		$smarty->assign('version', KB_VERSION." ".KB_RELEASE.$svn_rev);
		return $smarty->fetch(get_tpl("about_developers"));

	}

	function stats()
	{
		global $smarty;
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_kills");
		$row = $qry->getRow();
		$kills = $row['cnt'];
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_pilots");
		$row = $qry->getRow();
		$pilots = $row['cnt'];
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_corps");
		$row = $qry->getRow();
		$corps = $row['cnt'];
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_alliances");
		$row = $qry->getRow();
		$alliances = $row['cnt'];

		$smarty->assign('kills', $kills);
		$smarty->assign('items', $items);
		$smarty->assign('pilots', $pilots);
		$smarty->assign('corps', $corps);
		$smarty->assign('alliances', $alliances);
		return $smarty->fetch(get_tpl("about_stats"));
	}

	function theme()
	{
		global $smarty, $themeInfo;
		if(!isset($themeInfo))
		{
			global $themename;
			$themeInfo['name'] = $themename;
		}
		$smarty->assignByRef("themeInfo", $themeInfo);
		return $smarty->fetch(get_tpl("about_theme"));
	}

	function mods()
	{
		global $smarty, $modInfo;
		$smarty->assignByRef("mods", $modInfo);
		return $smarty->fetch(get_tpl("about_mods"));
	}

	function top()
	{
		global $smarty;
		return $smarty->fetch(get_tpl('about'));
	}

	function bottom()
	{
		global $smarty;
		return $smarty->fetch(get_tpl('about_bottom'));
	}
}


$about = new pAbout();
event::call("about_assembling", $about);
$html = $about->assemble();
$about->page->setContent($html);

$about->page->generate();
