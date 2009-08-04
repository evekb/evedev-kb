<?php
function update005(){
	// Blueprints and small fixes
	if (CURRENT_DB_UPDATE < "005" )
	{
		$qry = new DBQuery();
$qry->execute("INSERT IGNORE INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29249, 105, 'Magnate Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);");

$qry->execute("INSERT IGNORE INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29267, 111, 'Apotheosis Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);");

$qry->execute("INSERT IGNORE INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29338, 106, 'Omen Navy Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);");

$qry->execute("INSERT IGNORE INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29339, 106, 'Scythe Fleet Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);");

$qry->execute("INSERT IGNORE INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29341, 106, 'Osprey Navy Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);");

$qry->execute("INSERT IGNORE INTO `kb3_invtypes` (`typeID`, `groupID`, `typeName`, `description`, `icon`, `radius`, `mass`, `volume`, `capacity`, `portionSize`, `raceID`, `basePrice`, `marketGroupID`) VALUES (29345, 106, 'Exequror Navy Issue Blueprint', '', '0', 0, 0, 0.01, 0, 1, 0, 0, 0);");

$qry->execute("INSERT IGNORE INTO `kb3_dgmtypeattributes` (`typeID`, `attributeID`, `value`) VALUES ('29249', '422', '1');");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='180';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='181';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='182';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='183';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='184';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='228';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='229';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='230';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='231';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='232';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='277';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='278';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '50_11' WHERE `attributeID`='279';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '04_12' WHERE `attributeID`='193';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '04_12' WHERE `attributeID`='235';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '22_14' WHERE `attributeID`='108';");
$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '22_14' WHERE `attributeID`='197';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '07_15' WHERE `attributeID`='137';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '24_01' WHERE `attributeID`='77';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '22_08' WHERE `attributeID`='153';");

$qry->execute("UPDATE `kb3_dgmattributetypes` SET `icon` = '07_15' WHERE `attributeID`='484';");

	config::set("DBUpdate","005");

	}
}

