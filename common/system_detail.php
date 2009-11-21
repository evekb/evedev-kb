<?php
require_once('common/includes/class.system.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.pageAssembly.php');

class pSystemDetail extends pageAssembly
{
	
	function __construct()
	{
		parent::__construct();
		$this->sys_id = intval($_GET['sys_id']);
		global $smarty;
		$this->smarty = $smarty;
		$this->view =  preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['view']);
		$this->viewList = array();
		$this->menuOptions = array();
		
		$this->queue("start");
		$this->queue("map");
		$this->queue("killList");
	}
	
	//! Start constructing the page.

	/*! Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
		if (!$this->sys_id)
		{
			echo 'no valid id supplied<br/>';
			exit;
		}
		$this->system = new SolarSystem($this->sys_id);
		$this->menuOptions = array();
		$this->page = new Page('System details - '.$this->system->getName());
		$this->smarty->assign('sys_id',$this->sys_id);
	}
	
	function map()
	{
		return $this->smarty->fetch(get_tpl("system_detail_map"));
	}
	
	//! Build the killlists that are needed for the options selected.
	function killList()
	{

		if(isset($this->viewList[$this->view])) return call_user_func_array($this->viewList[$this->view], array(&$this));

		$klist = new KillList();
		$klist->setOrdered(true);
		if ($this->view == 'losses')
			involved::load($klist,'loss');
		else
			involved::load($klist,'kill');
		$klist->addSystem($this->system);
		if ($_GET['scl_id'])
			$klist->addVictimShipClass(intval($_GET['scl_id']));
		else
			$klist->setPodsNoobShips(config::get('podnoobs'));
		$klist->setLimit(20);

		if ($this->view == 'recent' || !isset($this->view))
			$html .= "<div class='kb-kills-header'>20 most recent kills</div>";
		elseif ($this->view == 'losses')
			$html .= "<div class='kb-kills-header'>All losses</div>";
		else
			$html .= "<div class='kb-kills-header'>All kills</div>";

		$this->pagesplitter = new PageSplitter($klist->getCount(), config::get('killcount'));

		$table = new KillListTable($klist);
		$html .= $table->generate();
		if (is_object($this->pagesplitter))
		{
			$html .= $this->pagesplitter->generate();
		}
		
		return $html;
	}
	
	//! Reset the assembly object to prepare for creating the context.
	function context()
	{
		parent::__construct();
		$this->queue("menu");
	}
	
	//! Build the menu.

	//! Additional options that have been set are added to the menu.
	function menu()
	{
		$menubox = new box("Menu");
		$menubox->setIcon("menu-item.gif");
		$menubox->addOption("caption","Navigation");
		$menubox->addOption("link","All kills", "?a=system_detail&amp;sys_id=".$this->sys_id."&amp;view=kills");
		$menubox->addOption("link","All losses", "?a=system_detail&amp;sys_id=".$this->sys_id."&amp;view=losses");
		$menubox->addOption("link","Recent Activity", "?a=system_detail&amp;sys_id=".$this->sys_id."&amp;view=recent");
		foreach($this->menuOptions as $options)
		{
			if(isset($options[2]))
				$menubox->addOption($options[0],$options[1], $options[2]);
			else
				$menubox->addOption($options[0],$options[1]);
		}
		return $menubox->generate();
	}
	//! Add an item to the menu in standard box format.

	/*!
	 *  Only links need all 3 attributes
	 * \param type Types can be caption, img, link, points.
	 * \param name The name to display.
	 * \param url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
	}

	//! Add a type of view to the options.

	/*!
	 * \param view The name of the view to recognise.
	 * \param callback The method to call when this view is used.
	 */
	function addView($view, $callback)
	{
		$this->viewList[$view] = $callback;
	}
}

$systemDetail = new pSystemDetail();
event::call("systemdetail_assembling", $systemDetail);
$html = $systemDetail->assemble();
$systemDetail->page->setContent($html);

$systemDetail->context();
event::call("systemdetail_context_assembling", $systemDetail);
$context = $systemDetail->assemble();
$systemDetail->page->addContext($context);

$systemDetail->page->generate();