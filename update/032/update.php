<?php
/**
 * re-assigns slots for all DESTROYED items on existing kills
 * to valid CCP inventory flags (see update 031 for DROPPED items)
 * 
 * updates items in batches of 50000, so needs to reload several times
 * 
 * update steps:
 *      1 - convert rigs
 * @package EDK
 */
function update032()
{
        // change directory to make the class-loader functional
        chdir("..");
	global $url, $smarty;
        
        static $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL = 50000;
        static $TABLE_NAME = "kb3_items_destroyed";
        
        
        $updateSteps = array(
            /** step 1: convert 5 to 92 (rigs) */
            1 => array(
                "flagOld" => 5,
                "flagNew" => InventoryFlag::$RIG_SLOT_1,
                "name" => "Rig Slots"
            ),
            /** step 2: convert 10 to 155 (Fleet Hangar)*/
            2 => array(
                "flagOld" => 10,
                "flagNew" => 155,
                "name" => "Fleet Hangar"
            ),
            /** step 3: convert 11 to 90 (Ship Maintenance Bay) */
            3 => array(
                "flagOld" => 11,
                "flagNew" => 90,
                "name" => "Ship Maintenance Bay"
            ),
            /** step 4: convert 12 to 133 (Fuel Bay)*/
            4 => array(
                "flagOld" => 12,
                "flagNew" => 133,
                "name" => "Fuel Bay"
            ),
            /** step 5: convert 13 to 134 (Ore Bay)*/
            5 => array(
                "flagOld" => 13,
                "flagNew" => 134,
                "name" => "Ore Bay"
            ),
            /** step 6: convert 14 to 143 (Ammo Bay)*/
            6 => array(
                "flagOld" => 14,
                "flagNew" => 143,
                "name" => "Ammo Bay"
            ),
            /** step 7: convert 15 to 136 (Mineral Bay) */
            7 => array(
                "flagOld" => 15,
                "flagNew" => 136,
                "name" => "Mineral Bay"
            ),
            /** step 8: convert 16 to 149 (PI Bay) */
            8 => array(
                "flagOld" => 16,
                "flagNew" => 149,
                "name" => "PI Bay"
            ),
            /** step 9: convert 1 to 27 (High Slots) */
            9 => array(
                "flagOld" => 1,
                "flagNew" => InventoryFlag::$HIGH_SLOT_1,
                "name" => "High Slots"
            ),
            /** step 10: convert 2 to 19 (Med Slots) */
            10 => array(
                "flagOld" => 2,
                "flagNew" => InventoryFlag::$MED_SLOT_1,
                "name" => "Med Slots"
            ),
            /** step 11: convert 3 to 11 (Low Slots) */
            11 => array(
                "flagOld" => 3,
                "flagNew" => InventoryFlag::$LOW_SLOT_1,
                "name" => "Low Slots"
            ),
            /** step 12: convert 7 to 125(Subsystms) */
            12 => array(
                "flagOld" => 7,
                "flagNew" => InventoryFlag::$SUB_SYSTEM_SLOT_1,
                "name" => "Sub Systems"
            ),
            /** step 13: convert 8 to 89 (Implants)*/
            13 => array(
                "flagOld" => 8,
                "flagNew" => InventoryFlag::$IMPLANT,
                "name" => "Implants"
            ),
            /** step 14: convert 9 to -1 (BPC Copies) */
            14 => array(
                "flagOld" => 9,
                "flagNew" => InventoryFlag::$COPY,
                "name" => "BPC Copies"
            ),
            /** step 15: convert 6 to 87 (Drone Bay) */
            15 => array(
                "flagOld" => 6,
                "flagNew" => InventoryFlag::$DRONE_BAY,
                "name" => "Drone Bay"
            ),
            /** step 16: convert 4 to 5 (Cargo) */
            16 => array(
                "flagOld" => 4,
                "flagNew" => InventoryFlag::$CARGO,
                "name" => "Cargo"
            )
        );


	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "032") {
            
                $configStatusKeyName = "032updatestatus";
                // get last conversion step from config
                $updateStep = config::get($configStatusKeyName);
                if(!$updateStep)
                {
                    // initialize
                    reset($updateSteps);
                    $updateStep = key($updateSteps);
                    config::set($configStatusKeyName, $updateStep);
                }

                // we're not done yet!
                if($updateStep <= count($updateSteps))
                {
                    $slotsToConvert = $updateSteps[$updateStep];
                    
                    $updateFlags = new DBPreparedQuery();
                    $updateFlags->prepare('UPDATE '.$TABLE_NAME.' SET itd_itl_id = ? WHERE itd_itl_id = ? LIMIT ?');
                    // bind parameter
                    $params = array('iii', &$slotsToConvert["flagNew"], &$slotsToConvert["flagOld"], &$NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL);
                    $updateFlags->bind_params($params);
                    if(!$updateFlags->execute())
                    {
                        $message = "Error while converting ".$slotsToConvert["name"]." for dropped items: ".$updateFlags->getErrorMsg();
                        $message .= "<br/>Update stopped.";
                        $smarty->assign('content', $message);
                        $smarty->display('update.tpl');
                        die();
                    }
                    
                    $numberOfRowsAffected = $updateFlags->affectedRows();
                    // no rows affected means we're done updating this flag
                    if($numberOfRowsAffected === 0)
                    {
                        // increase the step, so we can continue converting the nax flag
                        $updateStep++;
                        config::set($configStatusKeyName, $updateStep);
                        $slotsToConvertNext = $updateSteps[$updateStep];
                        $message = "Done converting ".$slotsToConvert["name"].", will continue with ".$slotsToConvertNext["name"];
                        $message .= "<br/>Page will reload in 1s";
                    }
                    
                    else
                    {
                        $message = "Converted $numberOfRowsAffected items in ".$slotsToConvert["name"];
                        $message .= "<br/>Will continue with next chunk in 1s";
                    }                    
                }
                
                else
                {
                    $message =  "Successfully converted all flags in ".$TABLE_NAME;
                    $message .= "<br/>Update 029 completed.";
                    $message .= "<br/>Page will reload in 1s";
                    
                    config::set("DBUpdate", "032");
                    $qry = DBFactory::getDBQuery(true);
                    $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '029' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '032'");
                    config::del($configStatusKeyName);
                    
                }

		$smarty->assign('refresh', 1);
		$smarty->assign('content', $message);
		$smarty->display('update.tpl');
		die();
	}
}

