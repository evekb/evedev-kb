<?php

namespace EDK\ESI;

use Swagger\Client\Configuration;

/**
 * Special configuration class for the EDK ESI client.
 * <p>
 * Provides additional options to be set, which are then used by the
 * customized EDK ESI client.
 */
class EsiConfiguration extends Configuration
{
    
    private static $defaultEsiConfiguration = null;
    
    /** 
     * @param int the maximum number of retries if 
     * an ESI call times out
     */
    protected $maxNumberOfRetries = 3;
    
    /** cURL timeout in seconds, deliberately chosen very short */
    protected static $CURL_TIMEOUT_DEFAULT = 3;
    
    /**
     * Creates a default EDK ESI configuration
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setCurlTimeout(self::$CURL_TIMEOUT_DEFAULT);
        $this->setUserAgent(EDK_USER_AGENT);
        $this->setDebugFile(KB_CACHEDIR . DIRECTORY_SEPARATOR . 'esi' . DIRECTORY_SEPARATOR . 'debug.log');
        if(defined('ESI_DEBUG'))
        {
            $this->setDebug(ESI_DEBUG);
        }
        // disable the expect header, because the ESI server reacts with HTTP 502
        $this->addDefaultHeader('Expect', '');
    }
    
    /**
     * Gets the maximum number of retries allowed if a 
     * call to ESI times out.
     * @return int 
     */
    function getMaxNumberOfRetries() 
    {
        return $this->maxNumberOfRetries;
    }

    /**
     * Sets the maximum number of retries allowed if a 
     * call to ESI times out.
     * 
     * @param int $numberOfRetries
     */
    function setMaxNumberOfRetries($numberOfRetries) 
    {
        $this->maxNumberOfRetries = intval($numberOfRetries);
    }
    
    /**
     * Gets an EDK ESI configuration with default settings.
     * 
     * @return EsiConfiguration
     */
    public static function getDefaultEsiConfiguration()
    {
        if (self::$defaultEsiConfiguration == null) 
        {
            self::$defaultEsiConfiguration = new EsiConfiguration();
        }

        return self::$defaultEsiConfiguration;
    }

}