<?php

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
$config = new Config(KB_SITE);

$url=$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$qry=new DBQuery(true);

// Add isk loss column to kb3_kills
if(empty($_GET['stage']))
{
	// Update price with items destroyed and ship value, excluding
	// blueprints since default cost is for BPO and BPC looks identical
	$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_ship` (
	  `kll_id` int(11) NOT NULL DEFAULT '0',
	  `value` float NOT NULL DEFAULT '0',
	  PRIMARY KEY (`kll_id`)
	)";
	$qry->execute($sql);
	$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_destroyed` (
	  `kll_id` int(11) NOT NULL DEFAULT '0',
	  `value` float NOT NULL DEFAULT '0',
	  PRIMARY KEY (`kll_id`)
	)";
	$qry->execute($sql);
	$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_dropped` (
	  `kll_id` int(11) NOT NULL DEFAULT '0',
	  `value` float NOT NULL DEFAULT '0',
	  PRIMARY KEY (`kll_id`)
	)";
	$qry->execute($sql);
	echo "<html><head>";
	echo "<meta http-equiv='refresh' content='5;url=http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?stage=2' />";
	echo"</head><body>Temporary tables created</body>";
	die();
}

if(intval($_GET['stage']))
{
	$qry->execute("LOCK TABLES tmp_price_ship WRITE, tmp_price_destroyed WRITE,
		tmp_price_dropped WRITE, kb3_kills WRITE, kb3_ships WRITE,
		kb3_items_destroyed WRITE, kb3_items_dropped WRITE,
		kb3_invtypes WRITE, kb3_item_price WRITE, kb3_config WRITE");
	if(intval($_GET['stage']) ==2)
	{
		$qry->execute("INSERT IGNORE INTO tmp_price_ship SELECT
			kll_id,if(isnull(price),shp_baseprice,price) FROM kb3_kills
			INNER JOIN kb3_ships ON kb3_ships.shp_id = kll_ship_id
			LEFT JOIN kb3_item_price on (shp_externalid = typeID)");
		echo "<html><head>";
		echo "<meta http-equiv='refresh' content='5;url=http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?stage=3' />";
		echo"</head><body>Ship prices prepared</body>";
		die();
	}
	if(intval($_GET['stage']) ==3)
	{
		$sql = "INSERT IGNORE INTO tmp_price_destroyed
			SELECT itd_kll_id,
			sum(if(typeName LIKE '%Blueprint%',0,if(isnull(itd_quantity),
			0,itd_quantity * if(price = 0 OR isnull(price),basePrice,price))))
			FROM kb3_items_destroyed
			LEFT JOIN kb3_item_price ON kb3_item_price.typeID = itd_itm_id
			LEFT JOIN kb3_invtypes ON itd_itm_id = kb3_invtypes.typeID
			GROUP BY itd_kll_id";
		$qry->execute($sql);
		echo "<html><head>";
		echo "<meta http-equiv='refresh' content='5;url=http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?stage=5' />";
		echo"</head><body>Destroyed item prices prepared</body>";
		die();
	}
	if(intval($_GET['stage']) ==5)
	{
		if(config::get('kd_droptototal'))
		{
			$sql = "INSERT INTO tmp_price_dropped
				SELECT itd_kll_id,
				sum(if(typeName LIKE '%Blueprint%',0,if(isnull(itd_quantity),
				0,itd_quantity * if(price = 0 OR isnull(price),basePrice,price))))
				FROM kb3_items_dropped
				LEFT JOIN kb3_item_price ON kb3_item_price.typeID = itd_itm_id
				LEFT JOIN kb3_invtypes ON itd_itm_id = kb3_invtypes.typeID
				GROUP BY itd_kll_id";
			$qry->execute($sql);
		}
		echo "<html><head>";
		echo "<meta http-equiv='refresh' content='5;url=http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?stage=7' />";
		echo "</head><body>Dropped item prices ";
		if(config::get('kd_droptototal')) echo "prepared.";
		else echo "ignored.";
		echo "</body>";
		die();
	}
	if(intval($_GET['stage']) ==7)
	{
		$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_isk_loss'");
		if(!$qry->recordCount()) $qry->execute("ALTER TABLE `kb3_kills` ADD `kll_isk_loss` FLOAT NOT NULL DEFAULT '0'");
		else $qry->execute("UPDATE kb3_kills SET kll_isk_loss = 0");
		echo "<html><head>";
		echo "<meta http-equiv='refresh' content='5;url=http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?stage=8' />";
		echo"</head><body>kll_isk_loss column created</body>";
		die();
	}
	if(intval($_GET['stage']) ==8)
	{$sql = 'UPDATE kb3_kills
			natural join tmp_price_ship
			left join tmp_price_destroyed on kb3_kills.kll_id = tmp_price_destroyed.kll_id ';
		if(config::get('kd_droptototal')) $sql .= ' left join tmp_price_dropped on kb3_kills.kll_id = tmp_price_dropped.kll_id ';
		$sql .= 'SET kb3_kills.kll_isk_loss = tmp_price_ship.value + ifnull(tmp_price_destroyed.value,0) ';
		if(config::get('kd_droptototal')) $sql .= ' + ifnull(tmp_price_dropped.value,0) ';
		$qry->execute ($sql);
		echo "<html><head>";
		echo "<meta http-equiv='refresh' content='5;url=http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?stage=9' />";
		echo"</head><body>Prices inserted</body>";
		die();
	}
	$qry->execute("UNLOCK TABLES");
	$qry->execute('DROP TABLE tmp_price_ship');
	$qry->execute('DROP TABLE tmp_price_destroyed');
	$qry->execute('DROP TABLE tmp_price_dropped');
	$qry->execute('DELETE FROM kb3_sum_alliance');
	$qry->execute('DELETE FROM kb3_sum_corp');
	killCache();
		echo "<html><head>";
		echo"</head><body>Summary tables emptied. Temporary tables removed. All done.</body>";

}

function killCache()
{
	if(!is_dir(KB_QUERYCACHEDIR)) return;
	$dir = opendir(KB_QUERYCACHEDIR);
	while ($line = readdir($dir))
	{
		if (strstr($line, 'qcache_qry') !== false)
		{
			@unlink(KB_QUERYCACHEDIR.'/'.$line);
		}
		elseif (strstr($line, 'qcache_tbl') !== false)
		{
			@unlink(KB_QUERYCACHEDIR.'/'.$line);
		}
	}
}
