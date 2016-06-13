<?php
/**
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

class EDKApiConnectionException extends Exception {}

// **********************************************************************************************************************************************
// **********************************************************************************************************************************************
// ****************                         					  GENERIC public static functionS                					             ****************
// **********************************************************************************************************************************************
// **********************************************************************************************************************************************
class API_Helpers
{
        public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_DEFAULT = 60;
        public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MAX = 200;
        public static $MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MIN = 10;
        
	// **********************************************************************************************************************************************
	// ****************                         					Convert ID -> Name               					             ****************
	// **********************************************************************************************************************************************
	public static function getTypeIDname($id, $update = false)
	{
		$id = intval($id);
		$sql = 'select inv.typeName from kb3_invtypes inv where inv.typeID = ' . $id;

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		if($qry->recordCount())
		{
			$row = $qry->getRow();

			return $row['typeName'];
		}
		else
		{
                        $Item = Item::fetchItem($id);
                        return $Item->getName();
		}
	}

	// **********************************************************************************************************************************************
	// ****************                         					Get GroupID from ID               					             ****************
	// **********************************************************************************************************************************************
	public static function getgroupID($id)
	{
		$sql = 'select inv.groupID from kb3_invtypes inv where inv.typeID = ' . $id;

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		$row = $qry->getRow();

		return $row['groupID'];
	}

	// **********************************************************************************************************************************************
	// ****************                         			    Convert groupID -> groupName           					             ****************
	// **********************************************************************************************************************************************
	public static function getgroupIDname($id)
	{
		$sql = 'select itt.itt_name from kb3_item_types itt where itt.itt_id = ' . $id;

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		$row = $qry->getRow();

		return $row['itt_name'];
	}

	// **********************************************************************************************************************************************
	// ****************                         					Get Skill Rank from ID                				             ****************
	// **********************************************************************************************************************************************
	public static function gettypeIDrank($id)
	{
		$sql = 'select att.value from kb3_dgmtypeattributes att where att.typeID = ' . $id . ' and att.attributeID = 275';

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		$row = $qry->getRow();

		return $row['value'];
	}

	// **********************************************************************************************************************************************
	// ****************                         			    Convert MoonID -> MoonName           					             ****************
	// **********************************************************************************************************************************************
	public static function getMoonName($id)
	{
		if ($id != 0)
		{
			$qry = DBFactory::getDBQuery();
			$sql = 'select itemName FROM kb3_moons WHERE itemID = '.$id;

			$qry->execute($sql);
			$row = $qry->getRow();

			return $row['itemName'];
		} else {
			return false;
		}
	}
        
        
        // **********************************************************************************************************************************************
	// ****************                         			    Convert MoonName -> MoonID           					             ****************
	// **********************************************************************************************************************************************
	public static function getMoonID($moonName)
	{
		if (!is_null($moonName))
		{
			$qry = DBFactory::getDBQuery();
			$sql = "select itemID FROM kb3_moons WHERE itemName = '".$qry->escape($moonName)."'";

			$qry->execute($sql);
			$row = $qry->getRow();

			return $row['itemID'];
		} else {
			return false;
		}
	}

	// **********************************************************************************************************************************************
	// ****************                         			    		Find Thunky          		 					             ****************
	// **********************************************************************************************************************************************
	public static function FindThunk()
	{ // round about now would probably be a good time for apologising about my sense of humour :oD
		$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "Captain Thunk"';

		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		$row = $qry->getRow();

		$pilot_id = $row['plt_id'];
		$pilot_charid = $row['plt_externalid'];

		if ( $pilot_id != 0 )	{
			return '<a href="'.KB_HOST.'/?a=pilot_detail&amp;plt_id=' . $pilot_id . '" ><font size="2">Captain Thunk</font></a>';
		} else {
			return "Captain Thunk";
		}
	}

	// **********************************************************************************************************************************************
	// ****************                         			         Update  CCP CorpID              					             ****************
	// **********************************************************************************************************************************************
	public static function Update_CorpID($corpName, $corpID)
	{
		if ( (strlen($corpName) != 0) && ($corpID != 0) )
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute( "SELECT * FROM `kb3_corps` WHERE `crp_name` = '" . slashfix($corpName) . "'");

			if ($qry->recordCount() != 0)
			{
				$row = $qry->getRow();
				if ($row['crp_external_id'] == NULL)
				{
					$qry->execute("update kb3_corps set crp_external_id = " . $corpID . " where `crp_id` = " . $row['crp_id']);
				}
			}
		}
	}

	// **********************************************************************************************************************************************
	// ****************                         			        Update CCP AllianceID            					             ****************
	// **********************************************************************************************************************************************
	public static function Update_AllianceID($allianceName, $allianceID)
	{
		if ( ($allianceName != "NONE") && ($allianceID != 0) )
		{
			$qry = DBFactory::getDBQuery();
			$qry->execute( "SELECT * FROM `kb3_alliances` WHERE `all_name` = '" . slashfix($allianceName) . "'");

			if ($qry->recordCount() != 0)
			{
				$row = $qry->getRow();
				if ($row['all_external_id'] == NULL)
				{
					$qry->execute("update kb3_alliances set all_external_id = " . $allianceID . " where `all_id` = " . $row['all_id']);
				}
			}
		}
	}

	// **********************************************************************************************************************************************
	// ****************                         		Convert GMT Timestamp to local time            					             ****************
	// **********************************************************************************************************************************************
	public static function ConvertTimestamp($timeStampGMT)
	{
		if (!config::get('API_ConvertTimestamp'))
		{
			// set gmt offset
			$gmoffset = (strtotime(date("M d Y H:i:s")) - strtotime(gmdate("M d Y H:i:s")));
			//if (!config::get('API_ForceDST'))
				//$gmoffset = $gmoffset + 3600;

			$cachetime = date("Y-m-d H:i:s",  strtotime($timeStampGMT) + $gmoffset);
		} else {
			$cachetime = $timeStampGMT;
		}

		return $cachetime;
	}
        
        public static function isCurlSupported()
        {
            if(in_array  ('curl', get_loaded_extensions()))
            {
                // check for SSL support with cURL
                $version = curl_version();
                return ($version['features'] & CURL_VERSION_SSL) && in_array  ('openssl', get_loaded_extensions());
            }
            
            else
            {
                return false;
            }
        }
        
        
        /**
         * executes a call against the XML API
         * @return true on success
         * @throws EDKApiConnectionException
         */
        public static function testXmlApiConnection()
        {
            $API_TESTING_CHARACTER_ID = 800263173;
            // connectivity check for XML API
            $apiIdToName = new API_IDtoName();
            // don't use caching for this
            PhealConfig::getInstance()->cache = new PhealNullCache();
            $apiIdToName->setIDs($API_TESTING_CHARACTER_ID);
            $apiIdToName->fetchXML();
            if(count($apiIdToName->getIDData()) > 0)
            {
                return true;
            }
            
            else
            {
                throw new EDKApiConnectionException($apiIdToName->getMessage(), $apiIdToName->getError());
            }
        }
        
        /**
         * executes a call against the CREST API
         * @return true on success
         * @throws EDKApiConnectionException
         */
        public static function testCrestApiConnection()
        {
            $CREST_TESTING_URL = CREST_PUBLIC_URL . Kill::$CREST_KILLMAIL_ENDPOINT . '33493676/553ac7e2aeabe48092bde10958de0a44dc6f35ef/';
            try
            {
                $kill = SimpleCrest::getReferenceByUrl($CREST_TESTING_URL);
                if(!is_null($kill) && (int)$kill->killID > 0)
                {
                    return true;
                }
                
                else
                {
                    throw new EDKApiConnectionException("CREST call returned invalid data!", -1);
                }
            }
            
            catch(Exception $e)
            {
                throw new EDKApiConnectionException($e->getMessage(), $e->getCode());
            }
        }
        
        
        
        /**
         * sets the preferred method for connecting to APIs
         * @return cURL or file
         */
        public static function autoSetApiConnectionMethod()
        {
            // has the connection method already been set?
            if(config::get('apiConnectionMethod'))
            {
                return;
            }
            
            // don't test cURL connection if cURL is not available
            if(!API_Helpers::isCurlSupported())
            {
                config::set('apiConnectionMethod', 'file');
                return;
            }
            
            try
            {
                // initialize with cURL setting
                config::set('apiConnectionMethod', 'curl');
                @API_Helpers::testXmlApiConnection();
                @API_Helpers::testCrestApiConnection();
            } 
            catch (Exception $ex) 
            {
                // cURL didn't work, fall back to file
                config::set('apiConnectionMethod', 'file');
            }
        }
        
        
         /**
         * sets the maximum number of kills to process per run (if not already set),
          * based on the time limit set in the PHP configuration
         */
        public static function autoSetMaxNumberOfKillsToProcess()
        {
            // has the maximum number of kills to process already been set?
            if(is_numeric(config::get('maxNumberOfKillsPerRun')))
            {
                return;
            }
            
            $timeLimit = ini_get('max_execution_time');
            $maxNumberOfKillsPerRun = self::$MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_DEFAULT;
            
            if($timeLimit !== FALSE)
            {
                // on average, we can fetch 2 kills per second (due to CREST response time limitations)
                $maxNumberOfKillsPerRun = min(array(floor($timeLimit * 1), self::$MAX_NUMBER_OF_KILLS_TO_PROCESS_PER_RUN_MAX));
            }
            
            config::set('maxNumberOfKillsPerRun', $maxNumberOfKillsPerRun);
        }
                
        
        
}