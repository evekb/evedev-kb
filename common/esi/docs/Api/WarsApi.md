# Swagger\Client\WarsApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getWars**](WarsApi.md#getWars) | **GET** /wars/ | List wars
[**getWarsWarId**](WarsApi.md#getWarsWarId) | **GET** /wars/{war_id}/ | Get war information
[**getWarsWarIdKillmails**](WarsApi.md#getWarsWarIdKillmails) | **GET** /wars/{war_id}/killmails/ | List kills for a war


# **getWars**
> int[] getWars($datasource, $max_war_id, $user_agent, $x_user_agent)

List wars

Return a list of wars  ---  Alternate route: `/v1/wars/`  Alternate route: `/legacy/wars/`  Alternate route: `/dev/wars/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\WarsApi();
$datasource = "tranquility"; // string | The server name you would like data from
$max_war_id = 56; // int | Only return wars with ID smaller than this.
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getWars($datasource, $max_war_id, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarsApi->getWars: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **max_war_id** | **int**| Only return wars with ID smaller than this. | [optional]
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

# **getWarsWarId**
> \Swagger\Client\Model\GetWarsWarIdOk getWarsWarId($war_id, $datasource, $user_agent, $x_user_agent)

Get war information

Return details about a war  ---  Alternate route: `/v1/wars/{war_id}/`  Alternate route: `/legacy/wars/{war_id}/`  Alternate route: `/dev/wars/{war_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\WarsApi();
$war_id = 56; // int | ID for a war
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getWarsWarId($war_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarsApi->getWarsWarId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **war_id** | **int**| ID for a war |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetWarsWarIdOk**](../Model/GetWarsWarIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getWarsWarIdKillmails**
> \Swagger\Client\Model\GetWarsWarIdKillmails200Ok[] getWarsWarIdKillmails($war_id, $datasource, $page, $user_agent, $x_user_agent)

List kills for a war

Return a list of kills related to a war  ---  Alternate route: `/v1/wars/{war_id}/killmails/`  Alternate route: `/legacy/wars/{war_id}/killmails/`  Alternate route: `/dev/wars/{war_id}/killmails/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\WarsApi();
$war_id = 56; // int | A valid war ID
$datasource = "tranquility"; // string | The server name you would like data from
$page = 1; // int | Which page to query, starting at 1, 2000 killmails per page.
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getWarsWarIdKillmails($war_id, $datasource, $page, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WarsApi->getWarsWarIdKillmails: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **war_id** | **int**| A valid war ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **page** | **int**| Which page to query, starting at 1, 2000 killmails per page. | [optional] [default to 1]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetWarsWarIdKillmails200Ok[]**](../Model/GetWarsWarIdKillmails200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

