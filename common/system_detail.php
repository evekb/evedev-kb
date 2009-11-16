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
		
		$this->queue("start");
		$this->queue("map");
		$this->queue("killList");
	}
	
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
	
	function killList()
	{
		$klist = new KillList();
		$klist->setOrdered(true);
		if ($_GET['view'] == 'losses')
			involved::load($klist,'loss');
		else
			involved::load($klist,'kill');
		$klist->addSystem($this->system);
		if ($_GET['scl_id'])
			$klist->addVictimShipClass(intval($_GET['scl_id']));
		else
			$klist->setPodsNoobShips(config::get('podnoobs'));
		$klist->setLimit(20);

		if ($_GET['view'] == 'recent' || !isset($_GET['view']))
			$html .= "<div class='kb-kills-header'>20 most recent kills</div>";
		elseif ($_GET['view'] == 'losses')
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
	
	function context()
	{
		parent::__construct();
		$this->queue("box");
	}
	
	function box()
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

	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = array($type, $name, $url);
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
?>