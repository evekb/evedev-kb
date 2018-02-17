<?php

/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * simple class for getting CREST references via URL
 */
class SimpleCrest
{
    
    /** timeout for fetching data from CREST
     * kills with many involved parties might take very long to fetch
     */
    public static $TIMEOUT = 45;
    /** request rate limit / second for public CREST */
    public static $RATE_LIMIT = 150;
    
    
    protected static $lastRequestTimestamp = null;
    /**
     * preferred method of getting data from CREST;
     * will fall back to file_get_contents if curl isn't available 
     * accepted values: curl, file
     */
    public static $HTTP_METHOD = "curl";
    
    
    public static $USER_AGENT = "EDK HTTP Requester, http://www.evekb.org/forum";
    
    
    protected static $curl;
    
    
    /**
     * takes a CREST URL and gets the referenced object
     * @param string $url
     * @return Object a decoded json object
     * @throws Exception
     */
    public static function getReferenceByUrl($url)
    {
        if($url == null)
        {
            return null;
        }
        
        self::respectRateLimit();

        $data = self::getCrestByCurl($url);
     
        if($data != NULL)
        {
            return json_decode($data);
        }
        
        return NULL;

    }
    
    /**
     * gets data from CREST using the given url and
     * cURL
     * @throws Exception
     */
    protected static function getCrestByCurl($url)
    {
        if(!self::$curl)
        {
            self::$curl = curl_init();
        }
        
        $headers = array();
        
        // ignore ssl peer verification
        if(substr($url,0,5) == "https")
        {
            //curl_setopt(self::$curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            // make sure we can verify the peer's certificatge
            curl_setopt(self::$curl, CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . '/cert/cacert.pem');
        }
        
        // set timeout
        curl_setopt(self::$curl, CURLOPT_TIMEOUT, self::$TIMEOUT);
        
        // allow all encodings
        curl_setopt(self::$curl, CURLOPT_ENCODING, "");

        // curl defaults
        curl_setopt(self::$curl, CURLOPT_URL, $url);
        curl_setopt(self::$curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$curl, CURLOPT_USERAGENT, self::$USER_AGENT);
        
        // call
        $result    = curl_exec(self::$curl);
        $errorNumber = curl_errno(self::$curl);
        $error = curl_error(self::$curl);
        
        // response http headers
        $httpCode = curl_getinfo(self::$curl, CURLINFO_HTTP_CODE);

        // http errors
        if($httpCode >= 400) {
            switch($httpCode) {
                case 400:
                case 503: 
                    return $result;
                    break;
                default:
            }            
            throw new Exception('Error getting data: HTTP '.$httpCode.', URL: '.$url, $httpCode);
        }
        
        // curl errors
        if($errorNumber)
        {
            throw new Exception('Error getting data: '.$error.'('.$errorNumber.')');
        }
        
        return $result;
    }
  
  
    /**
     * blocking waits until the next request may be sent
     */
    protected static function respectRateLimit()
    {
        // calculate the minimum interval between requests in seconds
        $minimumRequestInterval = 1.0 / self::$RATE_LIMIT;
        
        // initialize last request timestamp
        if(is_null(self::$lastRequestTimestamp))
        {
            self::$lastRequestTimestamp = microtime(true) - 1;
        }
        
        
        $timeSinceLastRequest = microtime(true) - self::$lastRequestTimestamp;
        // check if we have to wait before the next request
        if($timeSinceLastRequest < $minimumRequestInterval)
        {
            $timeToWait = ceil($minimumRequestInterval - $timeSinceLastRequest)*1000000;
            usleep($timeToWait);
        }
        
        // update last request time
        self::$lastRequestTimestamp = microtime(true);

        return;
    }
}
