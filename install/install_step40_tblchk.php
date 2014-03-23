<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php');die();}

function check_commenttable()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT count(*) FROM kb3_comments';
    $result = $qry->execute($query);
    if ($result)
    {
    	check_commenttablerow();
        return;
    }
    $query = 'CREATE TABLE `kb3_comments` (
	`ID` INT NOT NULL AUTO_INCREMENT ,
	`kll_id` INT NOT NULL ,
	`comment` TEXT NOT NULL ,
	`name` TINYTEXT NOT NULL ,
	`posttime` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
	PRIMARY KEY ( `ID` )
	) TYPE = MYISAM';
    $qry->execute($query);
}

function check_navigationtable()
{
	if (CORP_ID)
	{
	    $statlink = '?a=corp_detail';
	}
	elseif (ALLIANCE_ID)
	{
	    $statlink = '?a=alliance_detail';
	}

	$qry = DBFactory::getDBQuery();
	$query = 'SELECT count(*) FROM kb3_navigation';
	$result = mysql_query($query);
	if ($result)
	{
		$query = "SELECT hidden FROM kb3_navigation LIMIT 1";
		$result = @mysql_query($query);
		if (!$result)
		{
			 $qry->execute("ALTER TABLE `kb3_navigation` ADD `hidden` BOOL NOT NULL DEFAULT '0' AFTER `page` ;");
		}
		$query = "SELECT count(KBSITE) FROM kb3_navigation WHERE KBSITE = '".KB_SITE."'";
		$result = @mysql_query($query);
		if ($result)
		{
			$row = mysql_fetch_row($result);
			if ($row[0] == 0)
			{
				$queries = "INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr`,`page` ,`hidden`,`KBSITE`) VALUES ('top',1,'Home','?a=home','_self',1,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Campaigns','?a=campaigns','_self',2,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Contracts','?a=contracts','_self',3,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Post Mail','?a=post','_self',6,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Stats','$statlink','_self',7,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Awards','?a=awards','_self',8,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Standings','?a=standings','_self',9,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Search','?a=search','_self',10,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'Admin','?a=admin','_self',11,'ALL_PAGES',0,'".KB_SITE."');
					   		INSERT IGNORE INTO `kb3_navigation` (`nav_type`,`intern`,`descr` ,`url` ,`target`,`posnr` ,`page`,`hidden`,`KBSITE`) VALUES ('top',1,'About','?a=about','_self',12,'ALL_PAGES',0,'".KB_SITE."');";
			 	$query = explode("\n", $queries);
				foreach ($query as $querystring)
				{
					if ($string = trim(str_replace(');', ')', $querystring)))
					{
					    $qry->execute($string);
					}
	 			}
			}
	   		return;
		}
		$query = 'ALTER TABLE `kb3_navigation` ADD `KBSITE` VARCHAR( 16 ) NOT NULL';
        $qry->execute($query);
		$query = 'UPDATE `kb3_navigation` SET KBSITE = "'.KB_SITE.'" WHERE KBSITE LIKE "";';
        $qry->execute($query);
		return;
	}else{
		$query = 'CREATE TABLE `kb3_navigation` (
		`ID` INT NOT NULL AUTO_INCREMENT ,
		`nav_type` TINYTEXT NOT NULL,
		`intern` INT ( 1 ) NOT NULL,
		`descr` TINYTEXT NOT NULL ,
		`url` TINYTEXT NOT NULL ,
		`target` VARCHAR( 10 )  NOT NULL,
		`posnr` INT NOT NULL,
		`page` TINYTEXT NOT NULL,
		`hidden` BOOL NOT NULL DEFAULT "0",
		`KBSITE` VARCHAR ( 16 ) NOT NULL,
		PRIMARY KEY ( `ID` )
		) TYPE = MYISAM;';
		   $qry->execute($query);
		$queries = "INSERT IGNORE INTO `kb3_navigation` VALUES (1,'top',1,'Home','?a=home','_self',1,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (2,'top',1,'Campaigns','?a=campaigns','_self',2,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (3,'top',1,'Contracts','?a=contracts','_self',3,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (4,'top',1,'Kills','?a=kills','_self',4,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (5,'top',1,'Losses','?a=losses','_self',5,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (6,'top',1,'Post Mail','?a=post','_self',6,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (7,'top',1,'Stats','$statlink','_self',7,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (8,'top',1,'Awards','?a=awards','_self',8,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (9,'top',1,'Standings','?a=standings','_self',9,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (10,'top',1,'Search','?a=search','_self',10,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (11,'top',1,'Admin','?a=admin','_self',11,'ALL_PAGES',0,'".KB_SITE."');
	   		INSERT IGNORE INTO `kb3_navigation` VALUES (12,'top',1,'About','?a=about','_self',12,'ALL_PAGES',0,'".KB_SITE."');";
	 	$query = explode("\n", $queries);
		foreach ($query as $querystring)
		{
			if ($string = trim(str_replace(');', ')', $querystring)))
			{
			    $qry->execute($string);
			}
	 	}
	}
}

function check_commenttablerow()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT posttime FROM kb3_comments LIMIT 1';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'ALTER TABLE `kb3_comments` CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT';
        $qry->execute($query);
        return;
    }
    $query = 'ALTER TABLE `kb3_comments` ADD `posttime` TIMESTAMP DEFAULT \'0000-00-00 00:00:00\' NOT NULL';
    $qry->execute($query);
}

function check_shipvaltable()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT count(*) FROM kb3_ships_values';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $query = 'CREATE TABLE `kb3_ships_values` (
`shp_id` INT( 11 ) NOT NULL ,
`shp_value` BIGINT( 4 ) NOT NULL ,
PRIMARY KEY ( `shp_id` )
) TYPE = MYISAM ;';
    $qry->execute($query);
}

function check_invdetail()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT ind_sec_status FROM kb3_inv_detail LIMIT 1';
    $qry->execute($query);
    $len = mysql_field_len($qry->resid_,0);
    if ($len == 4)
    {
        $query = 'ALTER TABLE `kb3_inv_detail` CHANGE `ind_sec_status` `ind_sec_status` VARCHAR(5)';
        $qry->execute($query);
    }
}

function check_pilots()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT plt_name FROM kb3_pilots LIMIT 1';
    $qry->execute($query);
    $len = mysql_field_len($qry->resid_,0);
    if ($len == 32)
    {
        $query = 'ALTER TABLE `kb3_pilots` CHANGE `plt_name` `plt_name` VARCHAR(64) NOT NULL';
        $qry->execute($query);
    }
}

function check_contracts()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT ctd_sys_id FROM kb3_contract_details LIMIT 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_contract_details` ADD `ctd_sys_id` INT(11) NOT NULL DEFAULT \'0\'');

    $qry->execute('SHOW columns FROM `kb3_contract_details` LIKE \'ctd_ctr_id\'');
    $arr = $qry->getRow();
    if ($arr['Key'] == 'PRI')
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_contract_details` ADD INDEX (`ctd_ctr_id`) ');
}

function check_index()
{
    check_index_invcrp();
    check_index_invall();
    $qry = DBFactory::getDBQuery();
    $qry->execute('SHOW columns FROM kb3_item_types LIKE \'itt_id\'');
    $arr = $qry->getRow();
    if ($arr['Key'] == 'PRI')
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_item_types` ADD PRIMARY KEY ( `itt_id` ) ');
}

function check_index_invcrp()
{
    $qry = DBFactory::getDBQuery();
    $qry->execute('SHOW columns FROM kb3_inv_crp like \'inc_kll_id\'');
    $arr = $qry->getRow();
    if ($arr['Key'] == 'MUL')
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_inv_crp` ADD INDEX ( `inc_kll_id` ) ');
}

function check_index_invall()
{
    $qry = DBFactory::getDBQuery();
    $qry->execute('SHOW columns FROM kb3_inv_all like \'ina_kll_id\'');
    $arr = $qry->getRow();
    if ($arr['Key'] == 'MUL')
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_inv_all` ADD INDEX ( `ina_kll_id` ) ');
}

function check_tblstrct1()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT shp_description FROM kb3_ships LIMIT 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_ships` DROP `shp_description`';
    $qry->execute($query);
}
function check_tblstrct5()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT count(*) FROM kb3_standings';
    $result = mysql_query($query);
    if ($result)
    {
        $query = 'SELECT count(*) FROM kb3_standings WHERE sta_from=1 AND sta_to=1 AND sta_from_type=\'a\' AND
                  sta_to_type=\'c\'';
        $result = mysql_query($query);
        if ($result)
        {
            return;
        }
        $qry->execute('drop table kb3_standings');
    }
$query = 'CREATE TABLE `kb3_standings` (
  `sta_from` int(11) NOT NULL default \'0\',
  `sta_to` int(11) NOT NULL default \'0\',
  `sta_from_type` enum(\'a\',\'c\') NOT NULL default \'a\',
  `sta_to_type` enum(\'a\',\'c\') NOT NULL default \'a\',
  `sta_value` float NOT NULL default \'0\',
  `sta_comment` varchar(200) NOT NULL,
  KEY `sta_from` (`sta_from`)
) TYPE=MyISAM;';
    $qry->execute($query);
}

function check_tblstrct6()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT all_img FROM kb3_alliances LIMIT 1';
    $result = mysql_query($query);
    if (!$result)
    {
        return;
    }
    $query = 'ALTER TABLE `kb3_alliances` DROP `all_img`';
    $qry->execute($query);
}

function check_killtables()
{
    $qry = DBFactory::getDBQuery();
    $query = 'SELECT kll_dmgtaken FROM kb3_kills LIMIT 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_kills` ADD `kll_dmgtaken` INT(11) NOT NULL DEFAULT \'0\'');

    $qry = DBFactory::getDBQuery();
    $query = 'SELECT ind_dmgdone FROM kb3_inv_detail LIMIT 1';
    $result = mysql_query($query);
    if ($result)
    {
        return;
    }
    $qry->execute('ALTER TABLE `kb3_inv_detail` ADD `ind_dmgdone` INT(11) NOT NULL DEFAULT \'0\'');
}

check_commenttable();
check_navigationtable();
check_commenttablerow();
check_shipvaltable();
check_invdetail();
check_pilots();
check_contracts();
check_index();
check_index_invcrp();
check_index_invall();
check_tblstrct1();
check_tblstrct5();
check_tblstrct6();
check_killtables();
?>