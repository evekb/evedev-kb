<?php
/**
 * CorporationApi
 * PHP version 5
 *
 * @category Class
 * @package  Swagger\Client
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * EVE Swagger Interface
 *
 * An OpenAPI for EVE Online
 *
 * OpenAPI spec version: 0.2.6.dev1
 * 
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace EsiClient;

use \Swagger\Client\Configuration;
use \Swagger\Client\ApiClient;
use \Swagger\Client\ApiException;
use \Swagger\Client\ObjectSerializer;

/**
 * CorporationApi Class Doc Comment
 *
 * @category Class
 * @package  Swagger\Client
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class CorporationApi
{

    /**
     * API Client
     *
     * @var \Swagger\Client\ApiClient instance of the ApiClient
     */
    protected $apiClient;

    /**
     * Constructor
     *
     * @param \Swagger\Client\ApiClient|null $apiClient The api client to use
     */
    public function __construct(\Swagger\Client\ApiClient $apiClient = null)
    {
        if ($apiClient == null) {
            $apiClient = new ApiClient();
            $apiClient->getConfig()->setHost('https://esi.tech.ccp.is/latest');
        }

        $this->apiClient = $apiClient;
    }

    /**
     * Get API client
     *
     * @return \Swagger\Client\ApiClient get the API client
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * Set the API client
     *
     * @param \Swagger\Client\ApiClient $apiClient set the API client
     *
     * @return CorporationApi
     */
    public function setApiClient(\Swagger\Client\ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * Operation getCorporationsCorporationId
     *
     * Get corporation information
     *
     * @param int $corporation_id An Eve corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return \Swagger\Client\Model\GetCorporationsCorporationIdOk
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationId($corporation_id, $datasource = null)
    {
        list($response) = $this->getCorporationsCorporationIdWithHttpInfo($corporation_id, $datasource);
        return $response;
    }

    /**
     * Operation getCorporationsCorporationIdWithHttpInfo
     *
     * Get corporation information
     *
     * @param int $corporation_id An Eve corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return Array of \Swagger\Client\Model\GetCorporationsCorporationIdOk, HTTP status code, HTTP response headers (array of strings)
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdWithHttpInfo($corporation_id, $datasource = null)
    {
        // verify the required parameter 'corporation_id' is set
        if ($corporation_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $corporation_id when calling getCorporationsCorporationId');
        }
        // parse inputs
        $resourcePath = "/corporations/{corporation_id}/";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());

        // query params
        if ($datasource !== null) {
            $queryParams['datasource'] = $this->apiClient->getSerializer()->toQueryValue($datasource);
        }
        // path params
        if ($corporation_id !== null) {
            $resourcePath = str_replace(
                "{" . "corporation_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($corporation_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\Swagger\Client\Model\GetCorporationsCorporationIdOk',
                '/corporations/{corporation_id}/'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\Model\GetCorporationsCorporationIdOk', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdOk', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdNotFound', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdInternalServerError', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getCorporationsCorporationIdAlliancehistory
     *
     * Get alliance history
     *
     * @param int $corporation_id An EVE corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return \Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[]
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdAlliancehistory($corporation_id, $datasource = null)
    {
        list($response) = $this->getCorporationsCorporationIdAlliancehistoryWithHttpInfo($corporation_id, $datasource);
        return $response;
    }

    /**
     * Operation getCorporationsCorporationIdAlliancehistoryWithHttpInfo
     *
     * Get alliance history
     *
     * @param int $corporation_id An EVE corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return Array of \Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[], HTTP status code, HTTP response headers (array of strings)
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdAlliancehistoryWithHttpInfo($corporation_id, $datasource = null)
    {
        // verify the required parameter 'corporation_id' is set
        if ($corporation_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $corporation_id when calling getCorporationsCorporationIdAlliancehistory');
        }
        // parse inputs
        $resourcePath = "/corporations/{corporation_id}/alliancehistory/";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());

        // query params
        if ($datasource !== null) {
            $queryParams['datasource'] = $this->apiClient->getSerializer()->toQueryValue($datasource);
        }
        // path params
        if ($corporation_id !== null) {
            $resourcePath = str_replace(
                "{" . "corporation_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($corporation_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[]',
                '/corporations/{corporation_id}/alliancehistory/'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[]', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[]', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistoryInternalServerError', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getCorporationsCorporationIdIcons
     *
     * Get corporation icon
     *
     * @param int $corporation_id An EVE corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return \Swagger\Client\Model\GetCorporationsCorporationIdIconsOk
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdIcons($corporation_id, $datasource = null)
    {
        list($response) = $this->getCorporationsCorporationIdIconsWithHttpInfo($corporation_id, $datasource);
        return $response;
    }

    /**
     * Operation getCorporationsCorporationIdIconsWithHttpInfo
     *
     * Get corporation icon
     *
     * @param int $corporation_id An EVE corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return Array of \Swagger\Client\Model\GetCorporationsCorporationIdIconsOk, HTTP status code, HTTP response headers (array of strings)
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdIconsWithHttpInfo($corporation_id, $datasource = null)
    {
        // verify the required parameter 'corporation_id' is set
        if ($corporation_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $corporation_id when calling getCorporationsCorporationIdIcons');
        }
        // parse inputs
        $resourcePath = "/corporations/{corporation_id}/icons/";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());

        // query params
        if ($datasource !== null) {
            $queryParams['datasource'] = $this->apiClient->getSerializer()->toQueryValue($datasource);
        }
        // path params
        if ($corporation_id !== null) {
            $resourcePath = str_replace(
                "{" . "corporation_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($corporation_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\Swagger\Client\Model\GetCorporationsCorporationIdIconsOk',
                '/corporations/{corporation_id}/icons/'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\Model\GetCorporationsCorporationIdIconsOk', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdIconsOk', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 404:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdIconsNotFound', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdIconsInternalServerError', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getCorporationsCorporationIdMembers
     *
     * Get corporation members
     *
     * @param int $corporation_id A corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return \Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[]
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdMembers($corporation_id, $datasource = null)
    {
        list($response) = $this->getCorporationsCorporationIdMembersWithHttpInfo($corporation_id, $datasource);
        return $response;
    }

    /**
     * Operation getCorporationsCorporationIdMembersWithHttpInfo
     *
     * Get corporation members
     *
     * @param int $corporation_id A corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return Array of \Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[], HTTP status code, HTTP response headers (array of strings)
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdMembersWithHttpInfo($corporation_id, $datasource = null)
    {
        // verify the required parameter 'corporation_id' is set
        if ($corporation_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $corporation_id when calling getCorporationsCorporationIdMembers');
        }
        // parse inputs
        $resourcePath = "/corporations/{corporation_id}/members/";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());

        // query params
        if ($datasource !== null) {
            $queryParams['datasource'] = $this->apiClient->getSerializer()->toQueryValue($datasource);
        }
        // path params
        if ($corporation_id !== null) {
            $resourcePath = str_replace(
                "{" . "corporation_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($corporation_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // this endpoint requires OAuth (access token)
        if (strlen($this->apiClient->getConfig()->getAccessToken()) !== 0) {
            $headerParams['Authorization'] = 'Bearer ' . $this->apiClient->getConfig()->getAccessToken();
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[]',
                '/corporations/{corporation_id}/members/'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[]', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[]', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 403:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdMembersForbidden', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdMembersInternalServerError', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getCorporationsCorporationIdRoles
     *
     * Get corporation members
     *
     * @param int $corporation_id A corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return \Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[]
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdRoles($corporation_id, $datasource = null)
    {
        list($response) = $this->getCorporationsCorporationIdRolesWithHttpInfo($corporation_id, $datasource);
        return $response;
    }

    /**
     * Operation getCorporationsCorporationIdRolesWithHttpInfo
     *
     * Get corporation members
     *
     * @param int $corporation_id A corporation ID (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return Array of \Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[], HTTP status code, HTTP response headers (array of strings)
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsCorporationIdRolesWithHttpInfo($corporation_id, $datasource = null)
    {
        // verify the required parameter 'corporation_id' is set
        if ($corporation_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $corporation_id when calling getCorporationsCorporationIdRoles');
        }
        // parse inputs
        $resourcePath = "/corporations/{corporation_id}/roles/";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());

        // query params
        if ($datasource !== null) {
            $queryParams['datasource'] = $this->apiClient->getSerializer()->toQueryValue($datasource);
        }
        // path params
        if ($corporation_id !== null) {
            $resourcePath = str_replace(
                "{" . "corporation_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($corporation_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // this endpoint requires OAuth (access token)
        if (strlen($this->apiClient->getConfig()->getAccessToken()) !== 0) {
            $headerParams['Authorization'] = 'Bearer ' . $this->apiClient->getConfig()->getAccessToken();
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[]',
                '/corporations/{corporation_id}/roles/'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[]', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[]', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 403:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdRolesForbidden', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsCorporationIdRolesInternalServerError', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

    /**
     * Operation getCorporationsNames
     *
     * Get corporation names
     *
     * @param int[] $corporation_ids A comma separated list of corporation IDs (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return \Swagger\Client\Model\GetCorporationsNames200Ok[]
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsNames($corporation_ids, $datasource = null)
    {
        list($response) = $this->getCorporationsNamesWithHttpInfo($corporation_ids, $datasource);
        return $response;
    }

    /**
     * Operation getCorporationsNamesWithHttpInfo
     *
     * Get corporation names
     *
     * @param int[] $corporation_ids A comma separated list of corporation IDs (required)
     * @param string $datasource The server name you would like data from (optional, default to tranquility)
     * @return Array of \Swagger\Client\Model\GetCorporationsNames200Ok[], HTTP status code, HTTP response headers (array of strings)
     * @throws \Swagger\Client\ApiException on non-2xx response
     */
    public function getCorporationsNamesWithHttpInfo($corporation_ids, $datasource = null)
    {
        // verify the required parameter 'corporation_ids' is set
        if ($corporation_ids === null) {
            throw new \InvalidArgumentException('Missing the required parameter $corporation_ids when calling getCorporationsNames');
        }

        // parse inputs
        $resourcePath = "/corporations/names/";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = $this->apiClient->selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());

        // query params
        if (is_array($corporation_ids)) {
            $corporation_ids = $this->apiClient->getSerializer()->serializeCollection($corporation_ids, 'csv', true);
        }
        if ($corporation_ids !== null) {
            $queryParams['corporation_ids'] = $this->apiClient->getSerializer()->toQueryValue($corporation_ids);
        }
        // query params
        if ($datasource !== null) {
            $queryParams['datasource'] = $this->apiClient->getSerializer()->toQueryValue($datasource);
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath,
                'GET',
                $queryParams,
                $httpBody,
                $headerParams,
                '\Swagger\Client\Model\GetCorporationsNames200Ok[]',
                '/corporations/names/'
            );

            return array($this->apiClient->getSerializer()->deserialize($response, '\Swagger\Client\Model\GetCorporationsNames200Ok[]', $httpHeader), $statusCode, $httpHeader);
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsNames200Ok[]', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
                case 500:
                    $data = $this->apiClient->getSerializer()->deserialize($e->getResponseBody(), '\Swagger\Client\Model\GetCorporationsNamesInternalServerError', $e->getResponseHeaders());
                    $e->setResponseObject($data);
                    break;
            }

            throw $e;
        }
    }

}
