<?php
require_once('common/includes/class.ship.php');
require_once('common/includes/class.pageAssembly.php');

class pAbout extends pageAssembly
{
	function __construct()
	{
		parent::__construct();
		
		$this->queue("start");
		$this->queue("developers");
		$this->queue("stats");
		$this->queue("shipValues");
		$this->queue("finish");
	}
	
	function start()
	{
		$this->page = new Page("About");
	}
	
	function developers()
	{
		// Current active developers
		$currentDeveloper = array(
			'Hon Kovell',
			'mastergamer');

		// Inactive developers
		$developer = array(
			'exi',
			'Beansman',
			'Ralle030583');

		// Other contributors
		$contributor = array(
			'FriedRoadKill', // Parser, db updates and image packs
			'Karbowiak',
			'JaredC01',
			'liquidism',
			'Mitchman',
			'Coni',
			'bunjiboys',
			'EDG',
			'Captain Thunk' // API mod
		);
		sort($developer);
		sort($contributor);

		$this->smarty->assign_by_ref('current_developer', $currentDeveloper);
		$this->smarty->assign_by_ref('developer', $developer);
		$this->smarty->assign('contributor', $contributor);
		$this->smarty->assign('version', KB_VERSION." ".KB_RELEASE." rev ".SVN_REV);
	}
	
	function stats()
	{
		$qry = new DBQuery();
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_kills");
		$row = $qry->getRow();
		$kills = $row['cnt'];
		$qry->execute("SELECT SUM(itd_quantity) AS cnt FROM kb3_items_destroyed");
		$row = $qry->getRow();
		$items = $row['cnt'];
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_pilots");
		$row = $qry->getRow();
		$pilots = $row['cnt'];
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_corps");
		$row = $qry->getRow();
		$corps = $row['cnt'];
		$qry->execute("SELECT COUNT(*) AS cnt FROM kb3_alliances");
		$row = $qry->getRow();
		$alliances = $row['cnt'];

		$this->smarty->assign('kills', $kills);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('pilots', $pilots);
		$this->smarty->assign('corps', $corps);
		$this->smarty->assign('alliances', $alliances);
	}
	
	function shipValues()
	{
		$sql = "select scl_id
			from kb3_ship_classes
			where scl_class not in ( 'Drone', 'Unknown' )
			order by scl_value";

		$qry = new DBQuery();
		$qry->execute($sql);

		$shipcl = array();
		while ($row = $qry->getRow())
		{
			$shipclass = new ShipClass($row['scl_id']);
			$class = array();
			$class['name'] = $shipclass->getName();
			$class['value'] = number_format($shipclass->getValue() * 1000000,0,',','.');
			$class['points'] = number_format($shipclass->getPoints(),0,',','.');
			$class['valind'] = $shipclass->getValueIndicator();
			$shipcl[] = $class;
		}
		number_format($shipclass->getPoints(),0,',','.')."</td><td align='center'><img class='ship' alt='' src=\"" . $shipclass->getValueIndicator() . "\" border=\"0\" /></td></tr>";
		$this->smarty->assign('shipclass', $shipcl);
	}
	
	function finish()
	{
		return $this->smarty->fetch(get_tpl('about'));
	}
}


$about = new pAbout();
global $smarty;
$about->smarty = $smarty; //Because $smarty is a global we have to add it to the class as an instance variable and access it with $this.
event::call("about_assembling", $about);
$html = $about->assemble();
$about->page->setContent($html);

$about->page->generate();
?>
