<?php
// Upgrade an existing installation.

/*
Each upgrade is placed in a subfolder and subfolder/update.php is included then
function [subfoldername] is called. Official updates are numbered sequentially.
e.g. upgrade/012/
*/
die("Not implemented yet");
if(function_exists("set_time_limit"))
	set_time_limit(0);

define('DB_HALTONERROR', true);

require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.session.php');

$config = new Config(KB_SITE);
session::init();
$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
if($_SERVER['QUERY_STRING'] != "") $url .= '?'.$_SERVER['QUERY_STRING'];

// Define style.
$style = '<style type="text/css">
body{margin: 0px;  color: #fff9ff;  padding: 0px;  height: 100%;  background-color: #0D2323;}
font,th,td,p,a,div{  font-family: Verdana, Bitstream Vera Sans, Arial, Helvetica;}
a{  color: #ffffff;  text-decoration: underline;}
#content{  margin-top: 10px;  padding: 10px;  background: #3B5353;  font-size: 11px;}
#page-title{  margin: 5px;  padding-top: 3px;  height: 25px;  color: #ffffff;  border-bottom: 1px solid #ffffff;  font-size: 16px;  font-weight: bold;}
</style>
';

$header1 = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>';
$header2 = '<meta http-equiv="content-type" content="text/html; charset=UTF8">
<title>EVE Development Network Killboard Upgrade Script</title>
'.$style.'
</head>
<body>
<table align="center" bgcolor="#111111" border="0" cellspacing="1" style="height: 100%">
<tr style="height: 100%">
<td valign="top" style="height: 100%">
<img src="banner/revelations_gray.jpg" border="0">
<div id="page-title">Upgrade</div>
<table cellpadding="0" cellspacing="0" width="100%" border="0">
<tr><td valign="top"><div id="content">';

$header = $header1.'<meta http-equiv="refresh" content="5;url='.$url.'" >'.$header2;

$footer = '</div></td></tr></table>
<div class="counter"><font style="font-size: 9px;">&copy;2006-2009 <a href="http://www.eve-dev.net/" target="_blank">EVE Development Network</a></font></div>
</td></tr></table></body></html>';

if (!session::isAdmin())
{
    if (isset($_POST['usrpass']) && (crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD || $_POST['usrpass'] == ADMIN_PASSWORD))
    {
        session::create(true);

        header('Location: '.$url);
		die;
    }
	else
	{ 
		echo $header1.$header2; ?>
You must log in as admin to complete an upgrade.
<form method="post" action="<?php echo $url; ?>">
<table>
<tr>
  <td width="160"><b>Admin Password:</b></td>
  <td><input type="password" name="usrpass" maxlength="32"></td>
</tr>
<tr>
  <td width="160">&nbsp;</td>
  <td><input type="submit" name="submit" value="Login"></td>
</tr>
</table></form>
<?php
	echo $footer;
	die;
	}
}
$qry=new DBQuery(true);
define('CURRENT_DB_UPDATE', config::get("DBUpdate"));
define('LASTEST_DB_UPDATE', "011");
if (CURRENT_DB_UPDATE >= LASTEST_DB_UPDATE )
{
	echo $header1.$header2;
	echo"Board is up to date.<br><a href='".config::get('cfg_kbhost')."'>Return to your board</a>";
	echo $footer;
	die();
}
updateDB();
@touch ('install/install.lock');
	echo $header1.$header2;
echo "Upgrade complete.<br><a href='".config::get('cfg_kbhost')."'>Return to your board</a>";
echo $footer;
die();

function updateDB(){
	// if update nesseary run updates
	if (CURRENT_DB_UPDATE < LASTEST_DB_UPDATE ){
		killCache();
		update001();
		update002();
		update003();
		update004();
		update005();

//Start visual updates
		update007();
		update008();
		update009();
		update010();
		update011();
	}
}

/*
 * Too much has changed between update005 and current status for a clean
 * update006. Restarting from update007 in the hope that the differences
 * between 5 and 7 are worked out and an update006 implemented
 */

 function update001(){
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "001" )
	{
		require_once("common/includes/class.item.php");
		// Changing ShieldBooster Slot from None to Mid Slot
		$ShieldBoosterGroup = item::get_group_id("Small Shield Booster I");
		update_slot_of_group($ShieldBoosterGroup,0,2);

		// Changing Tracking Scripts Slot from None to Mid Slot
		$ScriptGroupID1 = item::get_group_id("Optimal Range");
		update_slot_of_group($ScriptGroupID1,0,2);

		// Changing Warp Disruption Scripts Slot from None to Mid Slot
		$ScriptGroupID2 = item::get_group_id("Focused Warp Disruption");
		update_slot_of_group($ScriptGroupID2,0,2);

		// Changing Tracking Disruption Scripts Slot from None to Mid Slot
		$ScriptGroupID3 = item::get_group_id("Optimal Range Disruption");
		update_slot_of_group($ScriptGroupID3,0,2);

		// Changing Sensor Booster Scripts Slot from None to Mid Slot
		$ScriptGroupID4 = item::get_group_id("Targeting Range");
		update_slot_of_group($ScriptGroupID4,0,2);

		// Changing Sensor Dampener Scripts Slot from None to Mid Slot
		$ScriptGroupID5 = item::get_group_id("Scan Resolution Dampening");
		update_slot_of_group($ScriptGroupID5,0,2);

		// Changing Energy Weapon Slot from None to High Slot
		$EnergyWeaponGroup = item::get_group_id("Gatling Pulse Laser I");
		update_slot_of_group($EnergyWeaponGroup,0,1);

		// Changing Group of Salvager I to same as Small Tractor Beam I
		$item = new Item();
		$item->lookup("Salvager I");
		$SalvagerTypeId =  $item->getId();
		$SalvagerGroup  =  item::get_group_id("Salvager I");
		$TractorBeam    =  item::get_group_id("Small Tractor Beam I");
		move_item_to_group($SalvagerTypeId,$SalvagerGroup ,$TractorBeam);

		//writing Update Status into ConfigDB
		config::set("DBUpdate","001");
	}
}

function update002(){
	// to correct the already existing Salvager in med slots.
	// missed it in update001
	if (CURRENT_DB_UPDATE < "002" )
	{
		require_once("common/includes/class.item.php");
		$SalvagerGroup  =  item::get_group_id("Salvager I");
		update_slot_of_group($SalvagerGroup,2,1);
		config::set("DBUpdate","002");
	}
}

function update003(){
	// Warefare Links and Command Prozessor were midslot items in install file, should be high slot
	if (CURRENT_DB_UPDATE < "003" )
	{
		require_once("common/includes/class.item.php");
		$WarfareLinkGroup  =  item::get_group_id("Skirmish Warfare Link - Rapid Deployment");
		update_slot_of_group($WarfareLinkGroup,2,1);
		config::set("DBUpdate","003");
	}
}

function update004(){
	// new trinity ships are wrong saved as T1 shipes
	if (CURRENT_DB_UPDATE < "004" )
	{
		$qry = new DBQuery();

		$query = "UPDATE kb3_ships
					INNER JOIN kb3_ship_classes ON scl_id = shp_class
					SET shp_techlevel = 2
					WHERE scl_class IN ('Electronic Attack Ship','Heavy Interdictor','Black Ops','Marauder','Heavy Interdictor','Jump Freighter')
					AND shp_techlevel = 1;";
		$qry->execute($query);
		config::set("DBUpdate","004");
	}
}

function update005(){
	// Blueprints and small fixes
	if (CURRENT_DB_UPDATE < "005" )
	{
		$qry = new DBQuery();
$query = <<<EOF
INSERT INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29249, 105, 'Magnate Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);

INSERT INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29267, 111, 'Apotheosis Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);

INSERT INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29338, 106, 'Omen Navy Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);

INSERT INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29339, 106, 'Scythe Fleet Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);

INSERT INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29341, 106, 'Osprey Navy Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);

INSERT INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29345, 106, 'Exequror Navy Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);

INSERT INTO `kb3_dgmtypeattributes` (`typeID`, `attributeID`, `value`) VALUES ('29249', '422', '1');

UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='180';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='181';

UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='182';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='183';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='184';

UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='228';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='229';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='230';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='231';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='232';

UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='277';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='278';
UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='279';

UPDATE `kb3_dgmattributetypes` SET `icon` = '04_12' WHERE `attributeID`='193';
UPDATE `kb3_dgmattributetypes` SET `icon` = '04_12' WHERE `attributeID`='235';

UPDATE `kb3_dgmattributetypes` SET `icon` = '22_14' WHERE `attributeID`='108';
UPDATE `kb3_dgmattributetypes` SET `icon` = '22_14' WHERE `attributeID`='197';

UPDATE `kb3_dgmattributetypes` SET `icon` = '07_15' WHERE `attributeID`='137';

UPDATE `kb3_dgmattributetypes` SET `icon` = '24_01' WHERE `attributeID`='77';

UPDATE `kb3_dgmattributetypes` SET `icon` = '22_08' WHERE `attributeID`='153';

UPDATE `kb3_dgmattributetypes` SET `icon` = '07_15' WHERE `attributeID`='484';
EOF;

	$qry->execute($query);
	config::set("DBUpdate","005");

	}
}

function update007()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "007" )
	{
		$qry = new DBQuery(true);
		if(is_null(config::get('007updatestatus')))
			config::set('007updatestatus',0);
		if(config::get('007updatestatus') <1)
		{
			// Add columns for external ids.
			$qry->execute("SHOW COLUMNS FROM kb3_alliances LIKE 'all_external_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_alliances` ".
					"ADD `all_external_id` INT( 11 ) UNSIGNED NULL ".
					"DEFAULT NULL , ADD UNIQUE ( all_external_id )";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_external_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_corps` ".
					"ADD `crp_external_id` INT( 11 ) UNSIGNED NULL ".
					"DEFAULT NULL , ADD UNIQUE ( crp_external_id )";
				$qry->execute($sql);
			}

			$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_external_id'");
			if(!$qry->recordCount())
			{
				$sql = "ALTER TABLE `kb3_kills` ".
					"ADD `kll_external_id` INT( 11 ) UNSIGNED NULL ".
					"DEFAULT NULL , ADD UNIQUE ( kll_external_id )";
				$qry->execute($sql);
			}
			config::set('007updatestatus',1);
			echo $header;
			echo "7. External ID columns added";
			echo $footer;
			die();
		}
		// Add isk loss column to kb3_kills
		if(config::get('007updatestatus') <8)
		{
			// Update price with items destroyed and ship value, excluding
			// blueprints since default cost is for BPO and BPC looks identical
			if(config::get('007updatestatus') <2)
			{
				$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_ship` (
				  `kll_id` int(11) NOT NULL DEFAULT '0',
				  `value` float NOT NULL DEFAULT '0',
				  PRIMARY KEY (`kll_id`)
				) ENGINE=MyISAM";
				$qry->execute($sql);
				$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_destroyed` (
				  `kll_id` int(11) NOT NULL DEFAULT '0',
				  `value` float NOT NULL DEFAULT '0',
				  PRIMARY KEY (`kll_id`)
				) ENGINE=MyISAM";
				$qry->execute($sql);
				$sql = "CREATE TABLE IF NOT EXISTS `tmp_price_dropped` (
				  `kll_id` int(11) NOT NULL DEFAULT '0',
				  `value` float NOT NULL DEFAULT '0',
				  PRIMARY KEY (`kll_id`)
				) ENGINE=MyISAM";
				$qry->execute($sql);
				config::set('007updatestatus',2);
			}
			$qry->execute("LOCK TABLES tmp_price_ship WRITE, tmp_price_destroyed WRITE,
				tmp_price_dropped WRITE, kb3_kills WRITE, kb3_ships WRITE,
				kb3_ships_values WRITE, kb3_items_destroyed WRITE, kb3_items_dropped WRITE,
				kb3_invtypes WRITE, kb3_item_price WRITE, kb3_config WRITE");
			if(config::get('007updatestatus') <3)
			{
				$qry->execute("INSERT IGNORE INTO tmp_price_ship select
					kll_id,if(isnull(shp_value),shp_baseprice,shp_value) FROM kb3_kills
					INNER JOIN kb3_ships ON kb3_ships.shp_id = kll_ship_id
					LEFT JOIN kb3_ships_values ON kb3_ships_values.shp_id = kll_ship_id");
				$qry->execute($sql);
				config::set('007updatestatus',3);
				echo $header;
				echo "7. Kill values: Ship prices calculated";
				echo $footer;
				die();
			}
			if(config::get('007updatestatus') <4)
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
				config::set('007updatestatus',4);
				echo $header;
				echo "7. Kill values: Destroyed item prices calculated";
				echo $footer;
				die();
			}
			if(config::get('007updatestatus') <5)
			{
				if(config::get('kd_droptototal'))
				{
					$action = "calculated";
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
				else $action = "ignored";
				config::set('007updatestatus',5);
				echo $header;
				echo "7. Kill values: Dropped item prices $action";
				echo $footer;
				die();
			}
			if(config::get('007updatestatus') <7)
			{
				$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_isk_loss'");
				if(!$qry->recordCount())
				{
					$qry->execute("ALTER TABLE `kb3_kills` ADD `kll_isk_loss` FLOAT NOT NULL DEFAULT '0'");
					config::set('007updatestatus',7);
					echo $header;
					echo "7. Kill values: ISK column created";
				echo $footer;
					die();
				}
				config::set('007updatestatus',7);
				echo $header;
				echo "7. Kill values: ISK column already exists.";
				echo $footer;
				die();
			}
			if(config::get('007updatestatus') <8)
			{
				// default step size
				$step = 8192;
				if(!config::get('007.8status'))
				{
					config::set('007.8status', 0);
					config::set('007.8step', $step);
				}
				// If we had to restart then halve the step size up to 4 times.
				if(config::get('007.8status') > 0 && config::get('007.8step') >= $step / 2^4)
					config::set('007.8step', config::get('007.8step') / 2);
				$qry->execute("SELECT max(kll_id) as max FROM kb3_kills");
				$row=$qry->getRow();
				$count=$row['max'];
				while(config::get('007.8status') < $count)
				{
					$sql = 'UPDATE kb3_kills
						natural join tmp_price_ship
						left join tmp_price_destroyed on kb3_kills.kll_id = tmp_price_destroyed.kll_id ';
					if(config::get('kd_droptototal')) $sql .= ' left join tmp_price_dropped on kb3_kills.kll_id = tmp_price_dropped.kll_id ';
					$sql .= 'SET kb3_kills.kll_isk_loss = tmp_price_ship.value + ifnull(tmp_price_destroyed.value,0) ';
					if(config::get('kd_droptototal')) $sql .= ' + ifnull(tmp_price_dropped.value,0) ';
					$sql .= ' WHERE kb3_kills.kll_id >= '.config::get('007.8status').' AND kb3_kills.kll_id < '.
						(intval(config::get('007.8status')) + intval(config::get('007.8step')));
					$qry->execute ($sql);
					config::set('007.8status',(intval(config::get('007.8status')) + intval(config::get('007.8step'))) );
				}
				config::del('007.8status');
				config::del('007.8step');
				$qry->execute("UNLOCK TABLES");
				$qry->execute('DROP TABLE tmp_price_ship');
				$qry->execute('DROP TABLE tmp_price_destroyed');
				$qry->execute('DROP TABLE tmp_price_dropped');
				config::set('007updatestatus',8);
				echo $header;
				echo "7. Kill values: Totals updated";
				echo $footer;
				die();
			}
		}
		if(config::get('007updatestatus') <9)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_fb_crp_id'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE `kb3_kills` DROP `kll_fb_crp_id`");
			config::set('007updatestatus',9);
			echo $header;
			echo "7. kll_fb_crp_id column dropped";
				echo $footer;
			die();
		}
		if(config::get('007updatestatus') <10)
		{
			$qry->execute("SHOW COLUMNS FROM kb3_kills LIKE 'kll_fb_all_id'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE `kb3_kills` DROP `kll_fb_all_id`");
			config::set('007updatestatus',10);
			echo $header;
			echo "7. kll_fb_all_id column dropped";
				echo $footer;
			die();
		}
		if(config::get('007updatestatus') <11)
		{
			// Drop unused columns
			$qry->execute("SHOW COLUMNS FROM kb3_corps LIKE 'crp_trial'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE kb3_corps DROP crp_trial");
			$qry->execute("SHOW COLUMNS FROM kb3_pilots LIKE 'plt_killpoints'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE kb3_pilots DROP plt_killpoints");
			$qry->execute("SHOW COLUMNS FROM kb3_pilots LIKE 'plt_losspoints'");
			if($qry->recordCount()) $qry->execute("ALTER TABLE kb3_pilots DROP plt_losspoints");
			config::set('007updatestatus',11);
			echo $header;
			echo "7. Unused crp and plt columns dropped";
				echo $footer;
			die();
		}

		// Add corp and alliance index to kb3_inv_detail
		$qry->execute("SHOW INDEX FROM kb3_inv_detail");

		$indexcexists = false;
		$indexaexists = false;
		while($testresult = $qry->getRow())
			if($testresult['Column_name'] == 'ind_crp_id')
				$indexcexists = true;
			if($testresult['Column_name'] == 'ind_all_id')
				$indexaexists = true;
		if(config::get('007updatestatus') <12)
		{
			if(!$indexcexists)
				$qry->execute("ALTER  TABLE `kb3_inv_detail` ADD INDEX ( `ind_crp_id` ) ");
			config::set('007updatestatus',12);
			echo $header;
			echo "7. kb3_inv_detail ind_crp_id index added";
				echo $footer;
			die();
		}
		if(config::get('007updatestatus') <13)
		{
			if(!$indexaexists)
				$qry->execute("ALTER  TABLE `kb3_inv_detail` ADD INDEX ( `ind_all_id` ) ");
			config::set('007updatestatus',13);
			echo $header;
			echo "7. kb3_inv_detail ind_all_id index added";
				echo $footer;
			die();
		}
		if(config::get('007updatestatus') <14)
		{
			// Add table for api cache
			$sql = "CREATE TABLE IF NOT EXISTS `kb3_apicache` (
				 `cfg_site` varchar(16) NOT NULL default '',
				 `cfg_key` varchar(32) NOT NULL default '',
				 `cfg_value` text NOT NULL,
				 PRIMARY KEY  (`cfg_site`,`cfg_key`)
				 )";
			$qry->execute($sql);
			$qry->execute("CREATE TABLE IF NOT EXISTS `kb3_apilog` (
				`log_site` VARCHAR( 20 ) NOT NULL ,
				`log_keyname` VARCHAR( 20 ) NOT NULL ,
				`log_posted` INT NOT NULL ,
				`log_errors` INT NOT NULL ,
				`log_ignored` INT NOT NULL ,
				`log_verified` INT NOT NULL ,
				`log_totalmails` INT NOT NULL ,
				`log_source` VARCHAR( 20 ) NOT NULL ,
				`log_type` VARCHAR( 20 ) NOT NULL ,
				`log_timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
				) ENGINE = MYISAM ");

			// set API update complete
			config::set('API_DBUpdate', '1');
			config::set('007updatestatus',14);
			echo $header;
			echo "7. API tables added";
				echo $footer;
			die();
		}
		if(config::get('007updatestatus') <15)
		{

			// Add subsystem slot
			$qry->execute("SELECT 1 FROM kb3_item_locations WHERE itl_id = 7");
			if(!$qry->recordCount())
			{
				$qry->execute("INSERT INTO `kb3_item_locations` (`itl_id`, `itl_location`) VALUES(7, 'Subsystem Slot')");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 954 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 955 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 956 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 957 LIMIT 1");
				$qry->execute("UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 958 LIMIT 1");
			}
			config::set('007updatestatus',15);
			echo $header;
			echo "7. Subsystem slots added";
				echo $footer;
			die();
		}
		if(config::get('007updatestatus') <16)
		{
			$qry->execute('SHOW TABLES');
			$qry2 = new DBQuery(true);
			while($row = $qry->getRow())
			{
				$tablename = implode($row);
				if($tablename == 'kb3_inv_all') $qry2->execute("TRUNCATE kb3_inv_all");
				if($tablename == 'kb3_inv_crp') $qry2->execute("TRUNCATE kb3_inv_crp");
				if($tablename == 'kb3_inv_plt') $qry2->execute("TRUNCATE kb3_inv_plt");
			}
			killCache();
			config::set("DBUpdate","007");
			$qry->execute("UPDATE kb3_config SET cfg_value = '007' WHERE cfg_key = 'DBUpdate'");
			config::del('007updatestatus');
			echo $header;
			echo "7. Empty tables truncated.<br>Update 007 completed.";
			echo $footer;
			die();
		}
	}
}
// Add unique name indices to alliance, corp and pilot
// Check kb3_inv_detail has correct indices
function update008()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "008" )
	{

		if(is_null(config::get('008updatestatus'))) config::set('008updatestatus',0);
		$qry = new DBQuery(true);

		if(config::get('008updatestatus') <1)
		{
			// Add pilot, corp and alliance index to kb3_inv_detail
			// Incomplete in update007
			$qry->execute("SHOW INDEXES FROM kb3_inv_detail");
			$indexcexists = false;
			$indexaexists = false;
			$indexpexists = false;
			$indexkexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Column_name'] == 'ind_kll_id' && $testresult['Seq_in_index'] == 1)
					$indexkexists = true;
				if($testresult['Column_name'] == 'ind_crp_id' && $testresult['Seq_in_index'] == 1)
					$indexcexists = true;
				if($testresult['Column_name'] == 'ind_all_id' && $testresult['Seq_in_index'] == 1)
					$indexaexists = true;
				if($testresult['Column_name'] == 'ind_plt_id' && $testresult['Seq_in_index'] == 1)
					$indexpexists = true;
			}
			
			if(!$indexkexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_kll_id`, `ind_order` ) ");
				echo $header;
				echo "8. kb3_inv_detail index id_order added";
				echo $footer;
				die();
			}
			if(!$indexcexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_crp_id` ) ");
				echo $header;
				echo "8. kb3_inv_detail index id_order added";
				echo $footer;
				die();
			}
			if(!$indexaexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_all_id` ) ");
				echo $header;
				echo "8. kb3_inv_detail index id_order added";
				echo $footer;
				die();
			}
			if(!$indexpexists)
			{
				$qry->execute("ALTER TABLE `kb3_inv_detail` ADD INDEX ( `ind_plt_id` ) ");
				echo $header;
				echo "8. kb3_inv_detail index id_order added";
				echo $footer;
				die();
			}
			config::set('008updatestatus', 1);
		}

		$qry->execute("SHOW INDEXES FROM kb3_corps");
		$indexaexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'crp_name' && $testresult['Non_unique'] == 0)
				$indexcexists = true;
		}
		if(!$indexcexists)
		{
			$qry->rewind();
			$indexexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'UPGRADE8_crp_name')
					$indexexists = true;
			}
			if(!$indexexists) $qry->execute("ALTER TABLE `kb3_corps` ADD INDEX `UPGRADE8_crp_name` ( `crp_name` ) ");
			$sqlcrp = 'select a.crp_id as newid, b.crp_id as oldid from kb3_corps a, kb3_corps b where a.crp_name = b.crp_name and a.crp_id < b.crp_id';

			if(config::get('008updatestatus') <2)
			{
				$qry->execute('update kb3_inv_detail join ('.$sqlcrp.') c on c.oldid = ind_crp_id set ind_crp_id = c.newid');
				config::set('008updatestatus', 2);
				echo $header;
				echo "8. Unique corp names: updated kb3_inv_detail";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <3)
			{
				$qry->execute('update kb3_pilots join ('.$sqlcrp.') c on (c.oldid = plt_crp_id) set plt_crp_id = c.newid');
				config::set('008updatestatus', 3);
				echo $header;
				echo "8. Unique corp names: updated kb3_pilots";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <4)
			{
				$qry->execute('update kb3_kills join ('.$sqlcrp.') c on (c.oldid = kll_crp_id) set kll_crp_id = c.newid');
				config::set('008updatestatus', 4);
				echo $header;
				echo "8. Unique corp names: updated kb3_kills";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <6)
			{
				$qry->execute('delete b from kb3_corps a, kb3_corps b where a.crp_name = b.crp_name and a.crp_id < b.crp_id');
				config::set('008updatestatus', 6);
				echo $header;
				echo "8. Unique corp names: updated kb3_corps";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <7)
			{
				$qry->execute("ALTER TABLE `kb3_corps` DROP INDEX `UPGRADE8_crp_name`");
				$qry->execute("SHOW INDEXES FROM kb3_corps");
				$indexcexists = false;
				while($testresult = $qry->getRow())
				{
					if($testresult['Column_name'] == 'crp_name' && $testresult['Seq_in_index'] == 1)
					{
						$indexcname = $testresult['Key_name'];
						$indexcexists = true;
					}
					// Don't replace a custom multi-column index.
					elseif($testresult['Key_name'] == $indexcname && $testresult['Seq_in_index'] == 2)
						$indexcexists = false;
				}
				if($indexcexists) $qry->execute("ALTER TABLE `kb3_corps` DROP INDEX `".$indexcname."`");
				$qry->execute("ALTER TABLE `kb3_corps` ADD UNIQUE INDEX ( `crp_name` ) ");


				config::set('008updatestatus', 7);
				echo $header;
				echo "8. Unique corp names: unique index added to kb3_corps";
				echo $footer;
				die();
			}
		}
		// Make kb3_alliances.all_name unique without losing kills
		$qry->execute("SHOW INDEXES FROM kb3_alliances");
		$indexaexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'all_name' && $testresult['Non_unique'] == 0)
				$indexaexists = true;
		}
		if(!$indexaexists)
		{
			$qry->rewind();
			$indexexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'UPGRADE8_all_name')
					$indexexists = true;
			}
			if(!$indexexists) $qry->execute("ALTER TABLE `kb3_alliances` ADD INDEX `UPGRADE8_all_name` ( `all_name` ) ");
			$sqlall = 'select a.all_id as newid, b.all_id as oldid from kb3_alliances a, kb3_alliances b where a.all_name = b.all_name and a.all_id < b.all_id';
			if(config::get('008updatestatus') <8)
			{
				$qry->execute('update kb3_inv_detail join ('.$sqlall.') c on c.oldid = ind_all_id set ind_all_id = c.newid');
				config::set('008updatestatus', 8);
				echo $header;
				echo "8. Unique all names: updated kb3_inv_detail";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <9)
			{
				$qry->execute('update kb3_corps join ('.$sqlall.') c on (c.oldid = crp_all_id) set crp_all_id = c.newid');
				config::set('008updatestatus', 9);
				echo $header;
				echo "8. Unique all names: updated kb3_corps";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <10)
			{
				$qry->execute('update kb3_kills join ('.$sqlall.') c on (c.oldid = kll_all_id) set kll_all_id = c.newid');
				config::set('008updatestatus', 10);
				echo $header;
				echo "8. Unique all names: updated kb3_kills";
				echo $footer;
				die();
			}

			if(config::get('008updatestatus') <12)
			{
				$qry->execute('delete b from kb3_alliances a, kb3_alliances b where a.all_name = b.all_name and a.all_id < b.all_id');
				config::set('008updatestatus', 12);
				echo $header;
				echo "8. Unique all names: updated kb3_alliances";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <13)
			{
				$qry->execute("ALTER TABLE `kb3_alliances` DROP INDEX `UPGRADE8_all_name`");
				$qry->execute("SHOW INDEXES FROM kb3_alliances");
				$indexaexists = false;
				while($testresult = $qry->getRow())
				{
					if($testresult['Column_name'] == 'all_name' && $testresult['Seq_in_index'] == 1)
					{
						$indexaname = $testresult['Key_name'];
						$indexaexists = true;
					}
					// Don't replace a custom multi-column index.
					elseif($testresult['Key_name'] == $indexaname && $testresult['Seq_in_index'] == 2)
						$indexaexists = false;
				}
				if($indexaexists) $qry->execute("ALTER TABLE `kb3_alliances` DROP INDEX `".$indexaname."`");
				$qry->execute("ALTER TABLE `kb3_alliances` ADD UNIQUE INDEX ( `all_name` ) ");
				config::set('008updatestatus', 13);
				echo $header;
				echo "8. Unique all names: unique index applied to kb3_alliances";
				echo $footer;
				die();
			}
		}

		// Make kb3_pilots.plt_name unique without losing kills
		$qry->execute("SHOW INDEXES FROM kb3_pilots");
		$indexaexists = false;
		while($testresult = $qry->getRow())
		{
			if($testresult['Column_name'] == 'plt_name' && $testresult['Non_unique'] == 0)
				$indexaexists = true;
		}
		if(!$indexaexists)
		{
			$qry->rewind();
			$indexexists = false;
			while($testresult = $qry->getRow())
			{
				if($testresult['Key_name'] == 'UPGRADE8_plt_name')
					$indexexists = true;
			}
			if(!$indexexists) $qry->execute("ALTER TABLE `kb3_pilots` ADD INDEX `UPGRADE8_plt_name` ( `plt_name` ) ");
			$sqlplt = 'select a.plt_id as newid, b.plt_id as oldid from kb3_pilots a, kb3_pilots b where a.plt_name = b.plt_name and a.plt_id < b.plt_id';
			if(config::get('008updatestatus') <14)
			{
				$qry->execute('update kb3_inv_detail join ('.$sqlplt.') c on c.oldid = ind_plt_id set ind_plt_id = c.newid');
				config::set('008updatestatus', 14);
				echo $header;
				echo "8. Unique plt names: updated kb3_inv_detail";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <15)
			{
				$qry->execute('update kb3_kills join ('.$sqlplt.') c on (c.oldid = kll_victim_id) set kll_victim_id = c.newid');
				config::set('008updatestatus', 15);
				echo $header;
				echo "8. Unique plt names: updated kb3_kills victim";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <16)
			{
				$qry->execute('update kb3_kills join ('.$sqlplt.') c on (c.oldid = kll_fb_plt_id) set kll_fb_plt_id = c.newid');
				config::set('008updatestatus', 16);
				echo $header;
				echo "8. Unique plt names: updated kb3_kills killer";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <17)
			{
				$qry->execute('delete b from kb3_pilots a, kb3_pilots b where a.plt_name = b.plt_name and a.plt_id < b.plt_id');
				config::set('008updatestatus', 17);
				echo $header;
				echo "8. Unique plt names: updated kb3_pilots";
				echo $footer;
				die();
			}
			if(config::get('008updatestatus') <18)
			{
				$qry->execute("ALTER TABLE `kb3_pilots` DROP INDEX `UPGRADE8_plt_name`");
				$qry->execute("SHOW INDEXES FROM kb3_pilots");
				$indexpexists = false;
				while($testresult = $qry->getRow())
				{
					if($testresult['Column_name'] == 'plt_name' && $testresult['Seq_in_index'] == 1)
					{
						$indexpname = $testresult['Key_name'];
						$indexpexists = true;
					}
					// Don't replace a custom multi-column index.
					elseif($testresult['Key_name'] == $indexpname && $testresult['Seq_in_index'] == 2)
						$indexpexists = false;
				}
				if($indexpexists)  $qry->execute("ALTER TABLE `kb3_pilots` DROP INDEX `".$indexpname."`");
				$qry->execute("ALTER TABLE `kb3_pilots` ADD UNIQUE INDEX ( `plt_name` ) ");
				config::set('008updatestatus', 18);
				echo $header;
				echo "8. Unique plt names: unique index applied to kb3_pilots.";
				echo $footer;
				die();
			}
		}
		config::set('cache_update', '*');
		config::set('cache_time', '10');

		killCache();
		config::set("DBUpdate", "008");
		$qry->execute("UPDATE kb3_config SET cfg_value = '008' WHERE cfg_key = 'DBUpdate'");
		config::del("008updatestatus");
		echo $header;
		echo "Update 008 completed.";
				echo $footer;
		die();
	}
}

// Add alliance and corp summary tables.
function update009()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "009" )
	{
		$qry = new DBQuery();
		$sql = "CREATE TABLE IF NOT EXISTS `kb3_sum_alliance` (
		  `asm_all_id` int(11) NOT NULL DEFAULT '0',
		  `asm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `asm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `asm_kill_isk` float NOT NULL DEFAULT '0',
		  `asm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `asm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`asm_all_id`,`asm_shp_id`)
		) ENGINE=InnoDB";
		$qry->execute($sql);
		$sql = "CREATE TABLE IF NOT EXISTS `kb3_sum_corp` (
		  `csm_crp_id` int(11) NOT NULL DEFAULT '0',
		  `csm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `csm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `csm_kill_isk` float NOT NULL DEFAULT '0',
		  `csm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `csm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`csm_crp_id`,`csm_shp_id`)
		) ENGINE=InnoDB";
		$qry->execute($sql);
		config::set("DBUpdate", "009");
		$qry->execute("UPDATE kb3_config SET cfg_value = '009' WHERE cfg_key = 'DBUpdate'");
		echo $header;
		echo "Update 009 completed.";
		echo $footer;
		die();
	}
}

// Add alliance and corp summary tables.
function update010()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "010" )
	{
		$qry = new DBQuery();
		$sql = "CREATE TABLE IF NOT EXISTS `kb3_sum_pilot` (
		  `psm_plt_id` int(11) NOT NULL DEFAULT '0',
		  `psm_shp_id` int(3) NOT NULL DEFAULT '0',
		  `psm_kill_count` int(11) NOT NULL DEFAULT '0',
		  `psm_kill_isk` float NOT NULL DEFAULT '0',
		  `psm_loss_count` int(11) NOT NULL DEFAULT '0',
		  `psm_loss_isk` float NOT NULL DEFAULT '0',
		  PRIMARY KEY (`psm_plt_id`,`psm_shp_id`)
		) ENGINE=InnoDB";
		$qry->execute($sql);

		config::set("DBUpdate", "010");
		echo $header;
		echo "Update 010 completed.";
		echo $footer;
		die();
	}
}

// Add alliance and corp summary tables.
function update011()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "011" )
	{
		$qry = new DBQuery();
		$sql = "ALTER TABLE `kb3_ships` CHANGE `shp_baseprice` `shp_baseprice` BIGINT( 12 ) NOT NULL DEFAULT '0'";
		$qry->execute($sql);

		config::set("DBUpdate", "011");
		echo $header;
		echo "Update 011 completed.";
		echo $footer;
		die();
	}
}

function update_slot_of_group($id,$oldSlot = 0 ,$newSlot){
	$qry  = new DBQuery();
	$query = "UPDATE kb3_item_types
				SET itt_slot = $newSlot WHERE itt_id = $id and itt_slot = $oldSlot;";
	$qry->execute($query);
	$query = "UPDATE kb3_items_destroyed
				INNER JOIN kb3_invtypes ON groupID = $id AND itd_itm_id = typeID
				SET itd_itl_id = $newSlot
				WHERE itd_itl_id = $oldSlot;";
	$qry->execute($query);

	$query = "UPDATE kb3_items_dropped
				INNER JOIN kb3_invtypes ON groupID = $id AND itd_itm_id = typeID
				SET itd_itl_id = $newSlot
				WHERE itd_itl_id = $oldSlot;";
	$qry->execute($query);
}

function move_item_to_group($id,$oldGroup ,$newGroup){
	$qry  = new DBQuery();
	$query = "UPDATE kb3_invtypes
				SET groupID = $newGroup
				WHERE typeID = $id AND groupID = $oldGroup;";
	$qry->execute($query);
}

function killCache()
{
	if(!is_dir(KB_CACHEDIR)) return;
	$dir = opendir(KB_CACHEDIR);
	while ($line = readdir($dir))
	{
		if (strstr($line, 'qcache_qry') !== false)
		{
			@unlink(KB_CACHEDIR.'/'.$line);
		}
		elseif (strstr($line, 'qcache_tbl') !== false)
		{
			@unlink(KB_CACHEDIR.'/'.$line);
		}
	}
}

?>