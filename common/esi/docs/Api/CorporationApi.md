# Swagger\Client\CorporationApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCorporationsCorporationId**](CorporationApi.md#getCorporationsCorporationId) | **GET** /corporations/{corporation_id}/ | Get corporation information
[**getCorporationsCorporationIdAlliancehistory**](CorporationApi.md#getCorporationsCorporationIdAlliancehistory) | **GET** /corporations/{corporation_id}/alliancehistory/ | Get alliance history
[**getCorporationsCorporationIdIcons**](CorporationApi.md#getCorporationsCorporationIdIcons) | **GET** /corporations/{corporation_id}/icons/ | Get corporation icon
[**getCorporationsCorporationIdMembers**](CorporationApi.md#getCorporationsCorporationIdMembers) | **GET** /corporations/{corporation_id}/members/ | Get corporation members
[**getCorporationsCorporationIdRoles**](CorporationApi.md#getCorporationsCorporationIdRoles) | **GET** /corporations/{corporation_id}/roles/ | Get corporation member roles
[**getCorporationsCorporationIdStructures**](CorporationApi.md#getCorporationsCorporationIdStructures) | **GET** /corporations/{corporation_id}/structures/ | Get corporation structures
[**getCorporationsNames**](CorporationApi.md#getCorporationsNames) | **GET** /corporations/names/ | Get corporation names
[**getCorporationsNpccorps**](CorporationApi.md#getCorporationsNpccorps) | **GET** /corporations/npccorps/ | Get npc corporations
[**putCorporationsCorporationIdStructuresStructureId**](CorporationApi.md#putCorporationsCorporationIdStructuresStructureId) | **PUT** /corporations/{corporation_id}/structures/{structure_id}/ | Update structure vulnerability schedule


# **getCorporationsCorporationId**
> \Swagger\Client\Model\GetCorporationsCorporationIdOk getCorporationsCorporationId($corporation_id, $datasource, $user_agent, $x_user_agent)

Get corporation information

Public information about a corporation  ---  Alternate route: `/v3/corporations/{corporation_id}/`  Alternate route: `/dev/corporations/{corporation_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | An Eve corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationId($corporation_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsCorporationId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An Eve corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdOk**](../Model/GetCorporationsCorporationIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdAlliancehistory**
> \Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[] getCorporationsCorporationIdAlliancehistory($corporation_id, $datasource, $user_agent, $x_user_agent)

Get alliance history

Get a list of all the alliances a corporation has been a member of  ---  Alternate route: `/v1/corporations/{corporation_id}/alliancehistory/`  Alternate route: `/legacy/corporations/{corporation_id}/alliancehistory/`  Alternate route: `/dev/corporations/{corporation_id}/alliancehistory/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | An EVE corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdAlliancehistory($corporation_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsCorporationIdAlliancehistory: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdAlliancehistory200Ok[]**](../Model/GetCorporationsCorporationIdAlliancehistory200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdIcons**
> \Swagger\Client\Model\GetCorporationsCorporationIdIconsOk getCorporationsCorporationIdIcons($corporation_id, $datasource, $user_agent, $x_user_agent)

Get corporation icon

Get the icon urls for a corporation  ---  Alternate route: `/v1/corporations/{corporation_id}/icons/`  Alternate route: `/legacy/corporations/{corporation_id}/icons/`  Alternate route: `/dev/corporations/{corporation_id}/icons/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | An EVE corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdIcons($corporation_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsCorporationIdIcons: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdIconsOk**](../Model/GetCorporationsCorporationIdIconsOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdMembers**
> \Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[] getCorporationsCorporationIdMembers($corporation_id, $datasource, $token, $user_agent, $x_user_agent)

Get corporation members

Read the current list of members if the calling character is a member.  ---  Alternate route: `/v2/corporations/{corporation_id}/members/`  Alternate route: `/legacy/corporations/{corporation_id}/members/`  Alternate route: `/dev/corporations/{corporation_id}/members/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | A corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdMembers($corporation_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsCorporationIdMembers: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| A corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdMembers200Ok[]**](../Model/GetCorporationsCorporationIdMembers200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdRoles**
> \Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[] getCorporationsCorporationIdRoles($corporation_id, $datasource, $token, $user_agent, $x_user_agent)

Get corporation member roles

Return the roles of all members if the character has the personnel manager role or any grantable role.  ---  Alternate route: `/v1/corporations/{corporation_id}/roles/`  Alternate route: `/legacy/corporations/{corporation_id}/roles/`  Alternate route: `/dev/corporations/{corporation_id}/roles/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | A corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdRoles($corporation_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsCorporationIdRoles: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| A corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdRoles200Ok[]**](../Model/GetCorporationsCorporationIdRoles200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdStructures**
> \Swagger\Client\Model\GetCorporationsCorporationIdStructures200Ok[] getCorporationsCorporationIdStructures($corporation_id, $datasource, $language, $page, $token, $user_agent, $x_user_agent)

Get corporation structures

Get a list of corporation structures  ---  Alternate route: `/v1/corporations/{corporation_id}/structures/`  Alternate route: `/legacy/corporations/{corporation_id}/structures/`  Alternate route: `/dev/corporations/{corporation_id}/structures/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | A corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$language = "en-us"; // string | Language to use in the response
$page = 56; // int | Which page to query, 250 structures per page
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdStructures($corporation_id, $datasource, $language, $page, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsCorporationIdStructures: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| A corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **page** | **int**| Which page to query, 250 structures per page | [optional]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdStructures200Ok[]**](../Model/GetCorporationsCorporationIdStructures200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsNames**
> \Swagger\Client\Model\GetCorporationsNames200Ok[] getCorporationsNames($corporation_ids, $datasource, $user_agent, $x_user_agent)

Get corporation names

Resolve a set of corporation IDs to corporation names  ---  Alternate route: `/v1/corporations/names/`  Alternate route: `/legacy/corporations/names/`  Alternate route: `/dev/corporations/names/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_ids = array(56); // int[] | A comma separated list of corporation IDs
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsNames($corporation_ids, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsNames: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_ids** | [**int[]**](../Model/int.md)| A comma separated list of corporation IDs |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsNames200Ok[]**](../Model/GetCorporationsNames200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsNpccorps**
> int[] getCorporationsNpccorps($datasource, $user_agent, $x_user_agent)

Get npc corporations

Get a list of npc corporations  ---  Alternate route: `/v1/corporations/npccorps/`  Alternate route: `/legacy/corporations/npccorps/`  Alternate route: `/dev/corporations/npccorps/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\CorporationApi();
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsNpccorps($datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->getCorporationsNpccorps: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putCorporationsCorporationIdStructuresStructureId**
> putCorporationsCorporationIdStructuresStructureId($corporation_id, $new_schedule, $structure_id, $datasource, $token, $user_agent, $x_user_agent)

Update structure vulnerability schedule

Update the vulnerability window schedule of a corporation structure  ---  Alternate route: `/v1/corporations/{corporation_id}/structures/{structure_id}/`  Alternate route: `/legacy/corporations/{corporation_id}/structures/{structure_id}/`  Alternate route: `/dev/corporations/{corporation_id}/structures/{structure_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CorporationApi();
$corporation_id = 56; // int | A corporation ID
$new_schedule = array(new PutCorporationsCorporationIdStructuresStructureIdNewSchedule()); // \Swagger\Client\Model\PutCorporationsCorporationIdStructuresStructureIdNewSchedule[] | New vulnerability window schedule for the structure
$structure_id = 789; // int | A structure ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $api_instance->putCorporationsCorporationIdStructuresStructureId($corporation_id, $new_schedule, $structure_id, $datasource, $token, $user_agent, $x_user_agent);
} catch (Exception $e) {
    echo 'Exception when calling CorporationApi->putCorporationsCorporationIdStructuresStructureId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| A corporation ID |
 **new_schedule** | [**\Swagger\Client\Model\PutCorporationsCorporationIdStructuresStructureIdNewSchedule[]**](../Model/PutCorporationsCorporationIdStructuresStructureIdNewSchedule.md)| New vulnerability window schedule for the structure |
 **structure_id** | **int**| A structure ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

