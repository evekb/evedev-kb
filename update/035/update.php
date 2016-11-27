<?php

require_once('../common/includes/class.xml.php');

/**
 * This database adds the singleton column to the table
 * kb3_dropped_items. 
 * Becuase of the potential of this table to be huge (8M entries),
 * simply adding the column would take too long in a single transaction
 * to be executed safely from a PHP script. The approach is to create a new
 * table with the column, but under a temporary name. Then copy all entries
 * from the old table to the new one, delete the old and rename the new table.
 * 
 * @package EDK
 */

class UpdateException extends Exception {}



function update035()
{
    global $url, $smarty;
    
    $DB_UPDATE = "035";
    $TABLE_NAME = "kb3_items_dropped";
    $TABLE_NAME_TEMP = $TABLE_NAME . "_temp";
    $NUMBER_OF_ENTRIES_PER_CHUNK = 200000;
     
        
    $updateSteps = array(
        1 => array(
            "stepDescription" => "create temporary table",
            "tableName" => $TABLE_NAME,
            "tableNameTemp" => $TABLE_NAME_TEMP,
            "chunkSize" => $NUMBER_OF_ENTRIES_PER_CHUNK,
            "dbUpdate" => $DB_UPDATE
        ),
        2 => array(
            "stepDescription" => "copy table",
            "tableName" => $TABLE_NAME,
            "tableNameTemp" => $TABLE_NAME_TEMP,
            "chunkSize" => $NUMBER_OF_ENTRIES_PER_CHUNK,
            "dbUpdate" => $DB_UPDATE
        ),
        3 => array(
            "stepDescription" => "delete old and rename new table",
            "tableName" => $TABLE_NAME,
            "tableNameTemp" => $TABLE_NAME_TEMP,
            "chunkSize" => $NUMBER_OF_ENTRIES_PER_CHUNK,
            "dbUpdate" => $DB_UPDATE
        )
    );
    
    // change directory to make the class-loader functional
    chdir("..");

    //Checking if this Update already done
    if (CURRENT_DB_UPDATE < $DB_UPDATE) 
    {
            
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
            $message .= "<font size=\"4\">Dropped items</font><br/>";
            // does the actual logic
            try
            {
                $result = performUpdateStep($updateStepNumber, $updateSteps[$updateStepNumber]);
                $message .= $result["stepMessage"];
                
                if($result["isStepComplete"] === TRUE)
                {
                     // increase the step, so we can continue with the process
                    $updateStepNumber++;
                    config::set($configStatusKeyName, $updateStepNumber);
                }
                
            }
            
            catch(UpdateException $e)
            {
                $message .= $e->getMessage();
                $message .= "<br/>Update stopped.";
                $smarty->assign('content', $message);
                $smarty->display('update.tpl');
                die();
            }
        }
        
        
        // finished all steps
        // conversion is done for this table
        else
        {
            $message =  "Successfully updated ".$TABLE_NAME;
            $message .= "<br/>Update $DB_UPDATE completed.";
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
* performs the actual logic for every update step
* @return array
*      - string stepMessage
*      - boolean isStepComplete
* @throws UpdateException
*/
function performUpdateStep($updateStepNumber, $context)
{

    $result = array(
        "stepMessage" => "",
        "isStepComplete" => FALSE
    );

    $result["stepMessage"] .= "Step $updateStepNumber:";
    $result["stepMessage"] .= $updateSteps[$updateStepNumber]."<br/>";

    // create the temprary table
    if($updateStepNumber == 1)
    {

        // we use the table structure from the setup package
        $xml = new sxml();
        $st = $xml->parse(file_get_contents('packages/database/'.$context["tableName"].'/table.xml'));
        $structureSql = $st['kb3']['structure'];
        
        // replace table name with temporary name
        $structureSql = str_replace($context["tableName"], $context["tableNameTemp"], $structureSql);

        $query = DBFactory::getDBQuery(true);
        // first drop temp table
        $sql = "DROP TABLE IF EXISTS ".$context["tableNameTemp"];
        $query->execute($sql);

        // create temp table
        $sqlResult = $query->execute($structureSql);
        if(!$sqlResult)
        {
            throw new UpdateException($query->getErrorMsg());
        }

        $result["stepMessage"] .= "Successfully created temporary table!";
        $result["isStepComplete"] = TRUE;
    }

    else if($updateStepNumber == 2)
    {
        $query = DBFactory::getDBQuery(TRUE);

        // get number of rows in target table
        $sql = "SELECT COUNT(*) AS num FROM ".$context["tableNameTemp"];
        $query->execute($sql);
        $row = $query->getRow();
        $numberOfRowsInTargetTable = $row["num"];

        // get number of rows in source table
        $sql = "SELECT COUNT(*) AS num FROM ".$context["tableName"];
        $query->execute($sql);
        $row = $query->getRow();
        $numberOfRowsInSourceTable = $row["num"];

        // are we done yet?
        if($numberOfRowsInTargetTable === $numberOfRowsInSourceTable)
        {
            $result["stepMessage"] .= "Successfully copied all entries from ".$context["tableName"]." to ".$context["tableNameTemp"];
            $result["isStepComplete"] = TRUE;
        }

        else if($numberOfRowsInTargetTable > $numberOfRowsInSourceTable)
        {
            // rollback: delete temp table and reset settings
            $query = DBFactory::getDBQuery(TRUE);

            $sql = "DROP TABLE IF EXISTS ".$context["tableNameTemp"];
            $query->execute($sql);
            config::del($context["dbUpdate"]."chunkNumber");
            config::del($context["dbUpdate"]."updatestatus");
            throw new UpdateException("Error: $numberOfRowsInTargetTable entries in target table, $numberOfRowsInSourceTable entries in source table!");
        }
        else
        {
            // calculate offset
            $chunkNumber = config::get($context["dbUpdate"]."chunkNumber");
            if(!$chunkNumber)
            {
                // empty temp table
                $query = DBFactory::getDBQuery(TRUE);
                $sql = "TRUNCATE TABLE ".$context["tableNameTemp"];
                $query->execute($sql);
                
                $chunkNumber = 0;
            }
            $offset = $context["chunkSize"] * $chunkNumber;
            // copy a chunk
            $sql = "INSERT INTO ".$context["tableNameTemp"]." SELECT itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id, '0' AS itd_singleton  FROM ".$context["tableName"]." ORDER BY itd_kll_id, itd_itm_id, itd_quantity ASC LIMIT $offset,".$context["chunkSize"];

            $query = DBFactory::getDBQuery(TRUE);
            $sqlResult = $query->execute($sql);

            $numberOfRowsCopied = $query->affectedRows();
            if(!$sqlResult)
            {
                throw new UpdateException($query->getErrorMsg());
            }

            $chunkNumber++;
            config::set($context["dbUpdate"]."chunkNumber", $chunkNumber);

            $result["stepMessage"] .= "Successfully copied ".$numberOfRowsCopied." rows from ".$context["tableName"]." to ".$context["tableNameTemp"];
            $result["isStepComplete"] = FALSE;
        }
    }

    else if($updateStepNumber == 3)
    {
        $query = DBFactory::getDBQuery(TRUE);
        $query->autocommit(FALSE);

        $sql = "DROP TABLE IF EXISTS ".$context["tableName"];
        if(!$query->execute($sql))
        {
            throw new UpdateException($query->getErrorMsg());
        }

        $sql = "RENAME TABLE ".$context["tableNameTemp"]." TO ".$context["tableName"];
        if(!$query->execute($sql))
        {
            throw new UpdateException($query->getErrorMsg());
        }
    
        $query->autocommit(TRUE);
        $result["stepMessage"] = "Successfully dropped old and renamed temporary table!";
        $result["isStepComplete"] = TRUE;
    }

    return $result;
}

