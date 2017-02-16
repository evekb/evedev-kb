<?php
namespace EDK\ESI;

use Swagger\Client\ApiClient;
use Swagger\Client\Configuration;
use Swagger\Client\ApiException;

require_once("common/esi/autoload.php");
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
    
    
    public function __construct(\Swagger\Client\Configuration $esiConfig = null) 
    {    
        if($esiConfig == null)
        {
            $esiConfig = Configuration::getDefaultConfiguration();
            $esiConfig->setCurlTimeout(60);
            $esiConfig->setUserAgent(EDK_USER_AGENT);
            // disable the expect header, because the ESI server reacts with HTTP 502
            $esiConfig->addDefaultHeader('Expect', '');
        }
        
        parent::__construct($esiConfig);     
    }
    
    /**
     * Sets the access token for OAuth
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->config->setAccessToken($accessToken);
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
            (array)$this->config->getDefaultHeaders(),
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

        $url = $this->config->getHost() . $resourcePath;

        $curl = curl_init();
        // set timeout, if needed
        if ($this->config->getCurlTimeout() != 0) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->config->getCurlTimeout());
        }
        // return the result on success, rather than just true
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // disable SSL verification, if needed
        if ($this->config->getSSLVerification() == false) {
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
        curl_setopt($curl, CURLOPT_USERAGENT, $this->config->getUserAgent());

        // debugging for curl
        if ($this->config->getDebug()) {
            error_log("[DEBUG] HTTP Request body  ~BEGIN~".PHP_EOL.print_r($postData, true).PHP_EOL."~END~".PHP_EOL, 3, $this->config->getDebugFile());

            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_STDERR, fopen($this->config->getDebugFile(), 'a'));
        } else {
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
        }

        // obtain the HTTP response headers
        curl_setopt($curl, CURLOPT_HEADER, 1);

        // Make the request
        $startTime = microtime(true);
        $response = curl_exec($curl);
        $http_header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $http_header = $this->httpParseHeaders(substr($response, 0, $http_header_size));
        $http_body = substr($response, $http_header_size);
        $response_info = curl_getinfo($curl);
        self::$totalEsiTime += microtime(true) - $startTime;
        
        // debug HTTP response body
        if ($this->config->getDebug()) {
            error_log("[DEBUG] HTTP Response body ~BEGIN~".PHP_EOL.print_r($http_body, true).PHP_EOL."~END~".PHP_EOL, 3, $this->config->getDebugFile());
        }

        // Handle the response
        if ($response_info['http_code'] == 0) {
            $curl_error_message = curl_error($curl);

            // curl_exec can sometimes fail but still return a blank message from curl_error().
            if (!empty($curl_error_message)) {
                $error_message = "API call to $url failed: $curl_error_message";
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
        return array($data, $response_info['http_code'], $http_header);
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
}