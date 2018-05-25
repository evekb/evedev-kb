<?php
namespace EDK\ESI;

require_once("common/esi/autoload.php");
require_once("common/phpfastcache/src/autoload.php");

use Swagger\Client\ApiClient;
use EDK\ESI\EsiConfiguration;
use Swagger\Client\ApiException;

use phpFastCache\CacheManager;

/**
 * EDK Wrapper class for the auto-generated ESI Client.
 * <br>
 * Set ups the configuration and instantiates an ESI Client.
 * By default, the data source set in constants.php is used.
 * To use API endpoints that require configuration, use <code>setAccessToken()</code>
 * before calling the API.
 * 
 * @author Salvoxia <salvoxia@blindfish.info>
 */
class ESI extends ApiClient 
{
    /** the data source for the ESI client */
    protected $dataSource = ESI_DATA_SOURCE;
    
    /** the total time [s] we spent talking to ESI during this request */
    protected static $totalEsiTime = 0.0;
    
    /** the PHPFastCache instance */
    protected static $cacheInstance;
    
    /** maximum number of retries for a specific call after a timeout occurred */
    protected static $MAX_NUMBER_OF_RETRIES = 3;
    
    /** @param EsiConfiguration the EDK ESI configuration */
    protected $esiConfig;
    
    protected static $curlMultiProcessor;
    
    public function __construct(EsiConfiguration $esiConfig = null) 
    {    
        if($esiConfig == null)
        {
            $esiConfig = EsiConfiguration::getDefaultEsiConfiguration();
        }
        
        $this->esiConfig = $esiConfig;
        
        // initialze phpFastCache instance
        if(!self::$cacheInstance)
        {
            $this->initCacheHandler();
        }
        
        parent::__construct($this->esiConfig);     
    }
        
    /**
     * Sets the access token for OAuth
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->getConfig()->setAccessToken($accessToken);
    }

    /**
     * Make the HTTP call (Sync)
     * Modification for EDK: Provide cURL with an up-to-date root CA bundle
     *
     * @param string $resourcePath path to method endpoint
     * @param string $method       method to call
     * @param array  $queryParams  parameters to be place in query URL
     * @param array  $postData     parameters to be placed in POST body
     * @param array  $headerParams parameters to be place in request header
     * @param string $responseType expected response type of the endpoint
     * @param string $endpointPath path to method endpoint before expanding parameters
     *
     * @throws \Swagger\Client\ApiException on a non 2xx response
     * @return mixed
     */
    public function callApi($resourcePath, $method, $queryParams, $postData, $headerParams, $responseType = null, $endpointPath = null)
    {

        $headers = array();

        // construct the http header
        $headerParams = array_merge(
            (array)$this->getConfig()->getDefaultHeaders(),
            (array)$headerParams
        );

        foreach ($headerParams as $key => $val) {
            $headers[] = "$key: $val";
        }

        // form data
        if ($postData and in_array('Content-Type: application/x-www-form-urlencoded', $headers)) {
            $postData = http_build_query($postData);
        } elseif ((is_object($postData) or is_array($postData)) and !in_array('Content-Type: multipart/form-data', $headers)) { // json model
            $postData = json_encode(\Swagger\Client\ObjectSerializer::sanitizeForSerialization($postData));
        }

        $url = $this->getConfig()->getHost() . $resourcePath;

        $curl = curl_init();
        // set timeout, if needed
        if ($this->getConfig()->getCurlTimeout() != 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->getConfig()->getCurlTimeout());
        }
        // return the result on success, rather than just true
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // disable SSL verification, if needed
        if ($this->getConfig()->getSSLVerification() == false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        else
        {
            // make sure we can verify the peer's certificatge
            curl_setopt($curl, CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . '/cert/cacert.pem');
        }

        if (!empty($queryParams)) {
            $url = ($url . '?' . http_build_query($queryParams));
        }

        if ($method == self::$POST) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        } elseif ($method == self::$HEAD) {
            curl_setopt($curl, CURLOPT_NOBODY, true);
        } elseif ($method == self::$OPTIONS) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "OPTIONS");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        } elseif ($method == self::$PATCH) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        } elseif ($method == self::$PUT) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        } elseif ($method == self::$DELETE) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        } elseif ($method != self::$GET) {
            throw new ApiException('Method ' . $method . ' is not recognized.');
        }
        curl_setopt($curl, CURLOPT_URL, $url);

        // Set user agent
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getConfig()->getUserAgent());

        // debugging for curl
        if ($this->config->getDebug()) {
            error_log("[DEBUG] HTTP Request body  ~BEGIN~".PHP_EOL.print_r($postData, true).PHP_EOL."~END~".PHP_EOL, 3, $this->config->getDebugFile());

            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_STDERR, fopen($this->getConfig()->getDebugFile(), 'a'));
        } else {
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
        }

        // obtain the HTTP response headers
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $cacheKey = md5($url);
        // check cache first
        if(self::$GET == $method)
        {
            $cachedData = $this->getFromCache($cacheKey);
            if(!is_null($cachedData))
            {
                return $cachedData;
            }
        }
        
        // Make the request
        $numberOfTries = 0;
        do {
            if($numberOfTries > 0 && $this->getConfig()->getDebug())
            {
                error_log("[DEBUG] Retry no ".$numberOfTries . PHP_EOL, 3, $this->getConfig()->getDebugFile());
            }
            $startTime = microtime(true);
            $response = $this->curlExecWithMulti($curl);
            $http_header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $http_header = $this->httpParseHeaders(substr($response, 0, $http_header_size));
            $http_body = substr($response, $http_header_size);
            $response_info = curl_getinfo($curl);
            $timeForRequest = microtime(true) - $startTime;
            self::$totalEsiTime += $timeForRequest;
            if($this->getConfig()->getDebug())
            {
                error_log("[DEBUG] Request took ".$timeForRequest."s" . PHP_EOL, 3, $this->getConfig()->getDebugFile());
            }
            
            if(isset($http_header['Warning']))
            {
                error_log("[WARNING] Endpoint ".$this->getConfig()->getHost() . $resourcePath." warning: ".$http_header['Warning']);
            }
            $numberOfTries++;
        } while ($numberOfTries <= $this->esiConfig->getMaxNumberOfRetries() && 
                    (
                        curl_errno($curl) == 28 // Timeout
                        ||   (curl_errno($curl) == 0 && !empty(curl_error($curl))) // in some cases, error code 0 is reported with a timeout error message 
                        || $response_info['http_code'] >= 500   // retry in case of server errors
                    )
                );
        
        // debug HTTP response body
        if ($this->getConfig()->getDebug()) {
            error_log("[DEBUG] HTTP Response body ~BEGIN~".PHP_EOL.print_r($http_body, true).PHP_EOL."~END~".PHP_EOL, 3, $this->getConfig()->getDebugFile());
        }

        // Handle the response
        if ($response_info['http_code'] == 0) {
            $curl_error_message = curl_error($curl);
            $curl_error_code = curl_errno($curl);
            // curl_exec can sometimes fail but still return a blank message from curl_error().
            if (!empty($curl_error_message)) {
                $error_message = "API call to $url failed: $curl_error_message (cURL error code: $curl_error_code, tried $numberOfTries times)";
            } else {
                $error_message = "API call to $url failed, but for an unknown reason. " .
                    "This could happen if you are disconnected from the network.";
            }

            $exception = new ApiException($error_message, 0, null, null);
            $exception->setResponseObject($response_info);
            throw $exception;
        } elseif ($response_info['http_code'] >= 200 && $response_info['http_code'] <= 299) {
            // return raw body if response is a file
            if ($responseType == '\SplFileObject' || $responseType == 'string') {
                return array($http_body, $response_info['http_code'], $http_header);
            }

            $data = json_decode($http_body);
            if (json_last_error() > 0) { // if response is a string
                $data = $http_body;
            }
        } else {
            $data = json_decode($http_body);
            if (json_last_error() > 0) { // if response is a string
                $data = $http_body;
            }

            throw new ApiException(
                "[".$response_info['http_code']."] Error connecting to the API ($url)",
                $response_info['http_code'],
                $http_header,
                $data
            );
        }
        
        $reply = array($data, $response_info['http_code'], $http_header);
        
        // cache the reply
        if($method == self::$GET)
        {
            $this->putIntoCache($cacheKey, $reply, new \DateTime($http_header['Expires']));
        }
        return $reply;
    }
    
    /**
     * Gets the ESI data source
     * @return string the ESI data source 
     */
    public function getDataSource() 
    {
        return $this->dataSource;
    }
    
    /**
     * Sets the ESI data source
     * @param string $dataSource
     */
    public function setDataSource($dataSource) 
    {
        $this->dataSource = $dataSource;
    }
    
    /**
     * Gets the total ESI time
     * @return int the total ESI time in seconds
     */
    static function getTotalEsiTime() 
    {
        return self::$totalEsiTime;
    }
    
    /**
     * Gets the reason for the API exception as text.
     * 
     * @param ApiException $e
     * @return string the reason for the API exception
     */
    public static function getApiExceptionReason($e)
    {
        $reason = $e->getMessage();
        $responseBody = $e->getResponseBody();
        if(!is_null($responseBody))
        {
            if(is_string($responseBody))
            {
                $reason = $responseBody;
            }
            else if(isset($responseBody->error))
            {
                $reason = $responseBody->error;
            }
        }
        return "ESI Exception: ".$e->getMessage()." ($reason, Code: ".$e->getCode().")";
    }
    
    
    protected function initCacheHandler()
    {
        // use Memcached
        if(defined('DB_USE_MEMCACHE') && DB_USE_MEMCACHE == true) 
        {
            self::$cacheInstance = CacheManager::getInstance('memcache', ['servers' => [
                [
                  'host' => \Config::get('cfg_memcache_server'),
                  'port' => \Config::get('cfg_memcache_port'),
                  // 'sasl_user' => false, // optional
                  // 'sasl_password' => false // optional
                ],
            ]]);
        } 

        // use Redis
        elseif(defined('DB_USE_REDIS') && DB_USE_REDIS == true) 
        {
            self::$cacheInstance =  CacheManager::getInstance('redis', [
                'host' => \Config::get('cfg_redis_server'),
                'port' => \Config::get('cfg_redis_port'),
            ]);
        } 
        
        // fall back to file caching
        else 
        {
            self::$cacheInstance =  CacheManager::getInstance('files', [
              "path" => getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . DIRECTORY_SEPARATOR . 'esi',
            ]);
        }
    }
    
    
    /**
     * Tries to get the object for the given key from the cache handler.
     * 
     * @param string $cacheKey the key for the object to retrieve
     * @return mixed the cached data or null
     */
    protected function getFromCache($cacheKey)
    {   
        if(isset(self::$cacheInstance))
        {
            $CachedObject = self::$cacheInstance->getItem($cacheKey);
            if(!is_null($CachedObject->get()))
            {
                return $CachedObject->get();
            }
            return null;
        }
    }
    
    /**
     * Stores the given data under the given key with the given
     * expiration date in the cache.
     * 
     * @param string $cacheKey  the key for the object to store
     * @param mixed $data the payload to cache
     * @param \DateTimeInterface $expirationDate the date of expiration
     */
    protected function putIntoCache($cacheKey, $data, $expirationDate)
    {
        if(isset(self::$cacheInstance))
        {
            $CachedObject = self::$cacheInstance->getItem($cacheKey);
            $CachedObject->set($data);
            $CachedObject->setExpirationDate($expirationDate);
            self::$cacheInstance->save($CachedObject);
        }
    }
    
    /**
     * Returns the cache instance used by the ESI Client
     * 
     * @return \phpFastCache\Core\Pool\ExtendedCacheItemPoolInterface
     */
    public static function getCacheInstance()
    {
        return self::$cacheInstance;
    }
    
    /**
     * Tries to use curl multi, if available.
     * Even though we do not execute requests concurrently, curl multi
     * keeps the HTTP connection open (if the requested server allows it),
     * so we save the overhead of establishing a new connection every time.
     * 
     * We use curl multi for this, instead or re-using a normal cURL handle,
     * because of the multitude of options that can be set for each request, which
     * would need to be reset for every new request.
     * 
     * If curl_multi_init() is not available, a simple curl_exec() is used.
     * 
     * @param resource $handle a cURL handle
     * @return mixed the result of the curl request
     */
    function curlExecWithMulti(&$handle) 
    {
        // Create a multi if necessary.
        if (empty(self::$curlMultiProcessor) && function_exists('curl_multi_init')) 
        {
          self::$curlMultiProcessor = curl_multi_init();
        }

        if(!empty(self::$curlMultiProcessor))
        {
            // Add the handle to be processed.
            curl_multi_add_handle(self::$curlMultiProcessor, $handle);

            // Do all the processing.
            $active = NULL;
            do 
            {
              $ret = curl_multi_exec(self::$curlMultiProcessor, $active);
            } while ($ret == CURLM_CALL_MULTI_PERFORM);

            while ($active && $ret == CURLM_OK)
            {
                // because of the way some PHP versions implement curl_multi_select,
                // it always returns -1; so we must wait ourselves; libcurl suggests
                // to wait 100ms
                if (curl_multi_select(self::$curlMultiProcessor) == -1) 
                {
                    usleep(100000);
                }

                do 
                {
                   $mrc = curl_multi_exec(self::$curlMultiProcessor, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            }

            $response = curl_multi_getcontent($handle);

            // remove the handle from the multi processor
            curl_multi_remove_handle(self::$curlMultiProcessor, $handle);
        }
      
        // fallback to default 
        else
        {
            $response = curl_exec($handle);
        }

        return $response;
    }
}