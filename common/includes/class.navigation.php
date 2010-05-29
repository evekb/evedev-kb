<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class Navigation
{
	private $type = 'top';
	private $page = '';
	private $site = null;
	private	$qry = null;

	function Navigation($site = KB_SITE)
	{
		$this->site = $site;
	}

	private function execQuery()
	{
		require_once('common/includes/class.killboard.php');
		$this->qry = DBFactory::getDBQuery();
		$query = "SELECT * FROM kb3_navigation".
			" WHERE nav_type = '$this->type'";

		$query .= " AND url != '?a=contracts'";

		if (Killboard::hasCampaigns() == false)
		{
			$query .= " AND url != '?a=campaigns'";
		}
		if (config::get('public_losses'))
		{
			$query .= " AND url != '?a=losses'";
		}
		if (!config::get('show_standings'))
		{
			$query .= " AND url != '?a=standings'";
		}
		if (config::get('public_stats')=='remove')
		{
			$query .= " AND url != '?a=self_detail'";
		}
		$query .= " AND (page = '".$this->page."' OR page = 'ALL_PAGES') AND hidden = 0";
		$query .= " AND KBSITE = '" . $this->site . "' ORDER BY posnr";
		$this->qry->execute($query);

		// If no navigation table was found then make one.
		if(!$this->qry->recordCount())
		{
			$this->check_navigationtable();
			$this->qry->execute($query);
		}
	}

	private function getRow()
	{
		return $this->qry->getRow();
	}

	public function setNavType($type)
	{
		$this->type = $type;
	}

	public function setPage($page)
	{
		$this->page = $page;
	}

	public function generateMenu()
	{
		$this->execQuery();

		$menu = new Menu();
		while ($row = $this->getRow())
		{
			// i know thats a bad hack
			$url = $row['url'] .'" target="'.$row['target'];
			$menu->add($url , $row['descr']);
		}
		return $menu;
	}

	private function check_navigationtable()
	{
		$sql = "select count(KBSITE) as cnt from kb3_navigation WHERE KBSITE = '".$this->site."'";
		$qry = DBFactory::getDBQuery(true);
		// return false if query fails
		if(!$qry->execute($sql)) return false;
		if(!($row = $qry->getRow())) return false;
		if($row['cnt'] == 0)
		{
			$sql = "INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr`,`page` ,`hidden`,`KBSITE`) VALUES".
				" ('top',1,'Home','?a=home','_self',1,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Campaigns','?a=campaigns','_self',2,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Post Mail','?a=post','_self',4,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Stats','?a=self_detail','_self',5,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Awards','?a=awards','_self',6,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Standings','?a=standings','_self',7,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Search','?a=search','_self',8,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'Admin','?a=admin','_self',9,'ALL_PAGES',0,'".$this->site."'),".
				" ('top',1,'About','?a=about','_self',10,'ALL_PAGES',0,'".$this->site."');";
			$qry->execute($sql);
		}
	}
}
