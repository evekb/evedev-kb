<?php


class UpdateException extends Exception {}



/**
 * converts the special inventory flag for BPCs for all DESTROYED items on existing kills
 * to "cargohold" flag and updates the singleton flag accordingly
 * 
 * updates items in batches of 50000, so needs to reload several times
 * 
 * update steps:
 *      1 - convert BPC copies
 * @package EDK
 */
function update037()
{
        // change directory to make the class-loader functional
        chdir("..");

    global $url, $smarty;
    
        $DB_UPDATE = "037";
    $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL = 50000;
    $TABLE_NAME = "kb3_items_destroyed";
        
        $updateSteps = array(
            /** step 1: convert -1 to 5 and singleton 2 (BPCs) */
            1 => array(
                "description" => "BPC Copies",
                "method" => "convertEDKBPCFlagAndUpdateSingleton",
                "tableName" => $TABLE_NAME,
                "numberOfItemsPerCall" => $NUMBER_OF_ITEMS_TO_UPDATE_PER_CALL
            )
        );


    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < $DB_UPDATE) {
            
                $configStatusKeyName = $DB_UPDATE."updatestatus";
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
                    $message .= "<br/>Update ".$DB_UPDATE." completed.";
                    $message .= "<br/>Page will reload in 1s";
                    
                    config::set("DBUpdate", $DB_UPDATE);
                    config::del($DB_UPDATE."chunkNumber");
                    $qry = DBFactory::getDBQuery(true);
                    $qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'DBUpdate', '".$DB_UPDATE."' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '".$DB_UPDATE."'");
                    
                }

        $smarty->assign('refresh', 1);
        $smarty->assign('content', $message);
        $smarty->display('update.tpl');
        die();
    }
        
}

/**
 * function for converting the special BPC flags to the cargo flag
 * and updating the singleton flag to 2
 * 
 * returns an array with a message to display for this step and a flag
 * indicating whether this step has been completed
 * 
 * @param array $slotsToConvert
 *      - int flagOld
 *      - int flagNew
 *      - string description
 *        - string tableName
 *        - string numberOfItemsPerCall
 * @return array
 *      - string stepMessage
 *      - boolean isStepComplete
 * @throws UpdateException
 */
function convertEDKBPCFlagAndUpdateSingleton($slotsToConvert)
{
    $result = array(
        "stepMessage" => "",
        "isStepComplete" => FALSE
    );

    $updateFlags = DBFactory::getDBQuery(true);
    $conversionResult = $updateFlags->execute('UPDATE '.$slotsToConvert["tableName"].' SET itd_itl_id = '.InventoryFlag::$CARGO.', itd_singleton = '.InventoryFlag::$SINGLETON_COPY.' WHERE itd_itl_id = '.InventoryFlag::$COPY.' LIMIT '.$slotsToConvert["numberOfItemsPerCall"]);
    
    if(!$conversionResult)
    {
        throw new UpdateException("Error while converting ".$slotsToConvert["description"]." for destroyed items: ".$updateFlags->getErrorMsg());
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

