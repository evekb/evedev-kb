<?php
/*
 * @package EDK
 */
class pAbout extends pageAssembly
{
	/** @var Page The Page object used to display this page. */
	public $page;

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
		$this->page = new Page(language::get('page_about'));
	}
	
	function developers()
	{
		global $smarty;
		// Current active developers
		$currentDeveloper = array(
			'Mini Mooo',
			'idun',
		);

		// Inactive developers
		$developer = array(
			'Hon Kovell', // Various stuff (EDK2-4)
			'exi', // Various stuff (EDK <2)
			'Beansman',
			'Ralle030583',
			'FriedRoadKill', // Parser, db updates and image packs
			'Captain Thunk' // API mod
			'mastergamer', // Various stuff
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
		return $smarty->fetch(get_tpl("about_developers"));

	}

	function stats()
	{
		global $smarty;
		$qry = DBFactory::getDBQuery();;
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_kills");
		$row = $qry->getRow();
		$kills = $row['cnt'];

		$smarty->assign('kills', $kills);
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
		$smarty->assign('version', KB_VERSION." ".KB_RELEASE);
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
