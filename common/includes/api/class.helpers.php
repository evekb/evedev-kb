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
// ****************                                               GENERIC public static functionS                                                 ****************
// **********************************************************************************************************************************************
// **********************************************************************************************************************************************
class API_Helpers
{

    // **********************************************************************************************************************************************
   
    // **********************************************************************************************************************************************
    // ****************                                 Convert GMT Timestamp to local time                                             ****************
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
            $CREST_TESTING_URL = CREST_PUBLIC_URL . Kill::$ESI_KILLMAIL_ENDPOINT . '33493676/553ac7e2aeabe48092bde10958de0a44dc6f35ef/';
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
}