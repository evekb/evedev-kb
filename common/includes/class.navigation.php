<?php

class Navigation{

	function Navigation()
	{
		// checking if a minimum navigation exists
		$this->check_navigationtable();

        $this->sql_start = "SELECT * FROM kb3_navigation";
        $this->sql_end = " AND KBSITE = '" . KB_SITE . "' ORDER BY posnr";
        $this->type_ = 'top';
	}

	function execQuery()
	{
		$this->qry = new DBQuery();
		$query = $this->sql_start;
		$query .= " WHERE nav_type = '$this->type_'";

		if (killboard::hasContracts() == false){
			$query .= " AND url != '?a=contracts'";
		}
		if (killboard::hasCampaigns() == false){
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
			$query .= " AND url != '?a=stats'";
		}
		$query .= " AND (page = '".$this->site_."' OR page = 'ALL_PAGES') AND hidden = 0";
		$query .= $this->sql_end;
		$this->qry->execute($query);
	}

	function getRow(){
		return $this->qry->getRow();
	}

	function setNavType($type)
	{
		$this->type_ = $type;
	}

	function setSite($site){
		$this->site_ = $site;
	}

	function generateMenu()
    {
    	$this->site_ = $site;
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

    function generateMenuBox()
    {
    	// TODO
    }

    function check_navigationtable(){
		if (CORP_ID)
		{
		    $statlink = '?a=corp_detail&amp;crp_id='.CORP_ID;
		}
		elseif (ALLIANCE_ID)
		{
		    $statlink = '?a=alliance_detail&amp;all_id='.ALLIANCE_ID;
		}
		$sql = "select count(KBSITE) as cnt from kb3_navigation WHERE KBSITE = '".KB_SITE."'";
		$qry = new DBQuery(true);
		// return false if query fails
		if(!$qry->execute($sql)) return false;
		if(!($row = $qry->getRow())) return false;
		if($row['cnt'] == 0)
		{
			$queries = "INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr`,`page` ,`hidden`,`KBSITE`) VALUES ('top',1,'Home','?a=home','_self',1,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Campaigns','?a=campaigns','_self',2,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Contracts','?a=contracts','_self',3,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Post Mail','?a=post','_self',4,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Stats','?a=self_detail','_self',5,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Awards','?a=awards','_self',6,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Standings','?a=standings','_self',7,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Search','?a=search','_self',8,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Admin','?a=admin','_self',9,'ALL_PAGES',0,'".KB_SITE."');
				   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'About','?a=about','_self',10,'ALL_PAGES',0,'".KB_SITE."');";
		 	$query = explode("\n", $queries);
		 	$qry = new DBQuery(true);
			foreach ($query as $querystring)
			{
				if ($string = trim(str_replace(');', ')', $querystring)))
				{
				    $qry->execute($string);
				}
			}
		}
	}
}
?>