<?php
/**
 * re-assigns slots for all DESTROYED items on existing kills
 * to valid CCP inventory flags (see update 032 for DROPPED items)
 * 
 * updates items in batches of 50000, so needs to reload several times;
 * updates 20 item types from manually posted kills in one step
 * 
 * update steps:
 *      1 - convert Rigs
 *      2 - convert Fleet Hangar (two step's mod)
 *      3 - convert Ship Maintenance Bay (two step's mod)
 *      4 - convert Fuel Bay (two step's mod)
 *      5 - convert Ore Bay (two step's mod)
 *      6 - convert Ammo Bay (two step's mod) 
 *      7 - convert Mineral Bay (two step's mod)
 *      8 - convert PI Bay (two step's mod)
 *      9 - convert High Slots (two step's mod)
 *      10 - convert Med Slots
 *      11 - convert Low Slots
 *      12 - convert Subsystems
 *      13 - convert Implants
 *      14 - convert BPC copies
 *      15 - convert Drone Bay
 *      16 - convert Cargo
 *      17 - prepare conversion of items from manually posted kills
 *      18  convert items from manually posted kills
 * @package EDK
 */
function update032()
{
        // change directory to make the class-loader functional
        chdir("..");
        // for this we need an up2date CCP DB first
        updateCCPDB();
        
	global $url, $smarty;
	
	$NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL = 50000;
	$TABLE_NAME = "kb3_items_destroyed";
        
        $updateSteps = array(
            /** step 1: convert 5 to 92 (rigs) */
            1 => array(
                "flagOld" => 5,
                "flagNew" => InventoryFlag::$RIG_SLOT_1,
                "description" => "Rig Slots",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 2: convert 10 to 155 (Fleet Hangar)*/
            2 => array(
                "flagOld" => 10,
                "flagNew" => 155,
                "description" => "Fleet Hangar",
                "method" => "convertEDKToCCPFlagDefault",
				"tableName" => $TABLE_NAME,
				"numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 3: convert 11 to 90 (Ship Maintenance Bay) */
            3 => array(
                "flagOld" => 11,
                "flagNew" => 90,
                "description" => "Ship Maintenance Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 4: convert 12 to 133 (Fuel Bay)*/
            4 => array(
                "flagOld" => 12,
                "flagNew" => 133,
                "description" => "Fuel Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 5: convert 13 to 134 (Ore Bay)*/
            5 => array(
                "flagOld" => 13,
                "flagNew" => 134,
                "description" => "Ore Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 6: convert 14 to 143 (Ammo Bay)*/
            6 => array(
                "flagOld" => 14,
                "flagNew" => 143,
                "description" => "Ammo Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 7: convert 15 to 136 (Mineral Bay) */
            7 => array(
                "flagOld" => 15,
                "flagNew" => 136,
                "description" => "Mineral Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 8: convert 16 to 149 (PI Bay) */
            8 => array(
                "flagOld" => 16,
                "flagNew" => 149,
                "description" => "PI Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 9: convert 1 to 27 (High Slots) */
            9 => array(
                "flagOld" => 1,
                "flagNew" => InventoryFlag::$HIGH_SLOT_1,
                "description" => "High Slots",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 10: convert 2 to 19 (Med Slots) */
            10 => array(
                "flagOld" => 2,
                "flagNew" => InventoryFlag::$MED_SLOT_1,
                "description" => "Med Slots",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 11: convert 3 to 11 (Low Slots) */
            11 => array(
                "flagOld" => 3,
                "flagNew" => InventoryFlag::$LOW_SLOT_1,
                "description" => "Low Slots",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 12: convert 7 to 125(Subsystms) */
            12 => array(
                "flagOld" => 7,
                "flagNew" => InventoryFlag::$SUB_SYSTEM_SLOT_1,
                "description" => "Sub Systems",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 13: convert 8 to 89 (Implants)*/
            13 => array(
                "flagOld" => 8,
                "flagNew" => InventoryFlag::$IMPLANT,
                "description" => "Implants",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 14: convert 9 to -1 (BPC Copies) */
            14 => array(
                "flagOld" => 9,
                "flagNew" => InventoryFlag::$COPY,
                "description" => "BPC Copies",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 15: convert 6 to 87 (Drone Bay) */
            15 => array(
                "flagOld" => 6,
                "flagNew" => InventoryFlag::$DRONE_BAY,
                "description" => "Drone Bay",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 16: convert 4 to 5 (Cargo) */
            16 => array(
                "flagOld" => 4,
                "flagNew" => InventoryFlag::$CARGO,
                "description" => "Cargo",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 17: slots in manually posted mails  */
            17 => array(
                "flagOld" => 0,
                "flagNew" => -20,
                "description" => "Slots in manually posted mails",
                "method" => "convertEDKToCCPFlagDefault",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            ),
            /** step 18: slots in manually posted mails  */
            18 => array(
                "description" => "Slots in manually posted mails",
                "method" => "convertSlotsFromManualMails",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            )
        );


	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "032") {
            
                $configStatusKeyName = "032updatestatus";
                // get last conversion step from config
                $updateStepNumber = config::get($configStatusKeyName);
                if(!$updateStepNumber)
                {
                    // initialize
                    reset($updateSteps);
                    $updateStepNumber = key($updateSteps);
                    config::set($configStatusKeyName, $updateStepNumber);
                }

                // we're not done yet!
                if($updateStepNumber <= count($updateSteps))
                {
                    $slotsToConvert = $updateSteps[$updateStepNumber];
                    $message = "<font size=\"4\">Destroyed items</font><br/>";
                    
                    try
                    {
                        $result = call_user_func($slotsToConvert["method"], $slotsToConvert);
                        $message .= $result["stepMessage"];
                        
                        if($result["isStepComplete"] === TRUE)
                        {
                             // increase the step, so we can continue converting the next flag
                            $updateStepNumber++;
                            config::set($configStatusKeyName, $updateStepNumber);
                            $slotsToConvertNext = $updateSteps[$updateStepNumber];
                        }
                    } 
                    catch (UpdateException $e) 
                    {
                        $message .= $e->getMessage();
                        $message .= "<br/>Update stopped.";
                        $smarty->assign('content', $message);
                        $smarty->display('update.tpl');
                        die();
                    }
                    $message .= "<br/>Page will reload in 1s";
                }

                // finished all steps
                // conversion is done for this table
                else
                {
                    $message =  "Converted all flags in ".$TABLE_NAME;
                    $message .= "<br/>Update 032 completed.";
                    $message .= "<br/>Page will reload in 1s";
                    
                    config::set("DBUpdate", "032");
                    $qry = DBFactory::getDBQuery(true);
                    $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '032' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '032'");
                    
                }

		$smarty->assign('refresh', 1);
		$smarty->assign('content', $message);
		$smarty->display('update.tpl');
		die();
	}
        
}

/**
 * function for updating a portion of items with a specific flag
 * to the corresponding CCP inventory flag
 * 
 * returns an array with a message to display for this step and a flag
 * indicating whether this step has been completed
 * 
 * @param array $slotsToUpdate
 *      - int flagOld
 *      - int flagNew
 *      - string description
 *		- string tableName
 *		- string numberOfItemsPerCall
 * @return array
 *      - string stepMessage
 *      - boolean isStepComplete
 * @throws UpdateException
 */
function convertEDKToCCPFlagDefault($slotsToConvert)
{
	$result = array(
		"stepMessage" => "",
		"isStepComplete" => FALSE
	);

	$updateFlags = DBFactory::getDBQuery(true);
	$conversionResult = $updateFlags->execute('UPDATE '.$slotsToConvert["tableName"].' SET itd_itl_id = '.$slotsToConvert["flagNew"].' WHERE itd_itl_id = '.$slotsToConvert["flagOld"].' LIMIT '.$slotsToConvert["numberOfItemsPerCall"]);
	
	if(!$conversionResult)
	{
		throw new UpdateException("Error while converting ".$slotsToConvert["description"]." for dropped items: ".$updateFlags->getErrorMsg());
	}

	$numberOfRowsAffected = $updateFlags->affectedRows();
	// no rows affected means we're done updating this flag
	if($numberOfRowsAffected === 0)
	{
		$result["stepMessage"] = "Done converting ".$slotsToConvert["description"];
		$result["isStepComplete"] = TRUE;
	}

	else
	{
		$result["stepMessage"] = "Converted $numberOfRowsAffected items in ".$slotsToConvert["description"];
		$result["stepMessage"] .= "<br/>Will continue with next chunk";
	} 
	return $result;
}

/**
 * conversion of items from manual killmails (with previously prepared location ID = -20)
 * 
 * returns an array with a message to display for this step and a flag
 * indicating whether this step has been completed
 * 
 * @param array $slotsToUpdate
 *      - string description
 *		- string tableName
 *		- string numberOfItemsPerCall
 * @return array
 *      - string stepMessage
 *      - boolean isStepComplete
 * @throws UpdateException
 */
function convertSlotsFromManualMails($slotsToConvert)
{
	$result = array(
		"stepMessage" => "",
		"isStepComplete" => FALSE
	);
	
	$qry = DBFactory::getDBQuery(true);
	// get 20 different items
	$qry->execute('SELECT DISTINCT itd_itm_id FROM '.$slotsToConvert["tableName"].' WHERE itd_itl_id = -20 LIMIT 20');

	if($qry->recordCount() > 0)
	{
		while($row = $qry->getRow())
		{
			$Item = Item::getByID($row['itd_itm_id']);

			if(!$Item->getName())
			{
				$result["stepMessage"] .= "<br/>Can't update slot for unknown item ".$row['itd_itm_id'];
				
				// convert it back to location ID 0
				$updateQuery = DBFactory::getDBQuery(true);
				$conversionResult = $updateQuery->execute('UPDATE '.$slotsToConvert["tableName"].' SET itd_itl_id = 0 WHERE itd_itl_id = -20 AND itd_itm_id = '.$row['itd_itm_id'].' LIMIT '.$slotsToConvert["numberOfItemsPerCall"]);
								
				if(!$conversionResult)
				{
					// we need to stop here, else we would be running in an endless loop
					throw new UpdateException("unable to convert slot back to 0 for item ID ".$row['itd_itm_id']." with error: ".$qry->getErrorMsg());
				}
			}
			
			else
			{
                                // avoid any caching issues by using a normal query
                                $slotLocationQuery = DBFactory::getDBQuery(true);
                                $slotLocationResult = $slotLocationQuery->execute("select itt_slot from kb3_item_types types
						inner join kb3_invtypes it ON it.groupID = types.itt_id
						where it.typeID = ".$row['itd_itm_id']);
                                
                                if(!$slotLocationResult)
                                {
                                    $location = 0;
                                }
                                
                                else
                                {
                                    $getSlotResult = $slotLocationQuery->getRow();
                                }

                                if (!$getSlotResult['itt_slot']) {
                                     // try getting location from parent item
                                    $slotLocationQuery->execute("select itt_slot from kb3_item_types
						inner join kb3_dgmtypeattributes d
						where itt_id = d.value
						and d.typeID = ".$row['itd_itm_id']."
						and d.attributeID in (137,602);");
                                    $getSlotResult = $slotLocationQuery->getRow();

                                    if (!$getSlotResult['itt_slot']) 
                                    {
                                            $location = 0;
                                    }
                                    
                                    else
                                    {
                                        $location = $getSlotResult['itt_slot'];
                                    }
                                }

                                else 
                                {
                                        $location = $getSlotResult['itt_slot'];
                                }

				// update slots for this item type, if location in database is -20
				$updateQuery = DBFactory::getDBQuery(true);
				$conversionResult = $updateQuery->execute('UPDATE '.$slotsToConvert["tableName"].' SET itd_itl_id = '.$location.' WHERE itd_itl_id = -20 AND itd_itm_id = '.$row['itd_itm_id'].' LIMIT '.$slotsToConvert["numberOfItemsPerCall"]);
				if(!$conversionResult)
				{
					throw new UpdateException("Failed to update slots for item ".$Item->getName()." with error: ".$updateQuery->getErrorMsg());
				}
			}
		}
		$result["stepMessage"] = "Converted a batch of items from manually posted kills";
	}

	// completed
	else
	{
		$result["stepMessage"] .= "Done converting ".$slotsToConvert["name"];
		$result["isStepComplete"] = TRUE;
	}
	
	return $result;
}

/**
 * checks and initializes update of the CCP DB package (if necessary)
 */
function updateCCPDB()
{
    global $url, $smarty;

    // @deprecated; use the getRequestScheme() method from globals.php
    // determine the request scheme
    $requestScheme = "http";
    if (isset($_SERVER['HTTPS'])) 
    {
        // Set to a non-empty value if the script was queried through the HTTPS protocol. 
        // ISAPI with IIS sets the value to "off", if the request was not madet throught the HTTPS protocol
        if (!empty($_SERVER['HTTPS']) && 'off' != strtolower($_SERVER['HTTPS']) && '' != trim($_SERVER['HTTPS']))
        {
            $requestScheme = "https";
        }
    } 

    // fallback: check the server port
    elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) 
    {
        $requestScheme = "https";
    }
    $requestScheme .= "://";
    
    // Check if the KB internal database structure needs updating
    // or if we need to install a new CCP DB
    if(config::get('CCPDbVersion') < KB_CCP_DB_VERSION)
    {       
            $package = 'CCPDB';
			if('update/'.is_dir($package)) require('update/'.$package.'/update.php');
			else
			{
				$smarty->assign('content', "Specified package does not exist.");
				$smarty->display('update.tpl');
			}
			die();
    }
}

