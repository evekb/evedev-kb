# Swagger\Client\SovereigntyApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getSovereigntyCampaigns**](SovereigntyApi.md#getSovereigntyCampaigns) | **GET** /sovereignty/campaigns/ | List sovereignty campaigns
[**getSovereigntyMap**](SovereigntyApi.md#getSovereigntyMap) | **GET** /sovereignty/map/ | List sovereignty of systems
[**getSovereigntyStructures**](SovereigntyApi.md#getSovereigntyStructures) | **GET** /sovereignty/structures/ | List sovereignty structures


# **getSovereigntyCampaigns**
> \Swagger\Client\Model\GetSovereigntyCampaigns200Ok[] getSovereigntyCampaigns($datasource, $user_agent, $x_user_agent)

List sovereignty campaigns

Shows sovereignty data for campaigns.  ---  Alternate route: `/v1/sovereignty/campaigns/`  Alternate route: `/legacy/sovereignty/campaigns/`  Alternate route: `/dev/sovereignty/campaigns/`   ---  This route is cached for up to 5 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\SovereigntyApi();
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getSovereigntyCampaigns($datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SovereigntyApi->getSovereigntyCampaigns: ', $e->getMessage(), PHP_EOL;
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

[**\Swagger\Client\Model\GetSovereigntyCampaigns200Ok[]**](../Model/GetSovereigntyCampaigns200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getSovereigntyMap**
> \Swagger\Client\Model\GetSovereigntyMap200Ok[] getSovereigntyMap($datasource, $user_agent, $x_user_agent)

List sovereignty of systems

Shows sovereignty information for solar systems  ---  Alternate route: `/v1/sovereignty/map/`  Alternate route: `/legacy/sovereignty/map/`  Alternate route: `/dev/sovereignty/map/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\SovereigntyApi();
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getSovereigntyMap($datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SovereigntyApi->getSovereigntyMap: ', $e->getMessage(), PHP_EOL;
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

[**\Swagger\Client\Model\GetSovereigntyMap200Ok[]**](../Model/GetSovereigntyMap200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getSovereigntyStructures**
> \Swagger\Client\Model\GetSovereigntyStructures200Ok[] getSovereigntyStructures($datasource, $user_agent, $x_user_agent)

List sovereignty structures

Shows sovereignty data for structures.  ---  Alternate route: `/v1/sovereignty/structures/`  Alternate route: `/legacy/sovereignty/structures/`  Alternate route: `/dev/sovereignty/structures/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\SovereigntyApi();
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getSovereigntyStructures($datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SovereigntyApi->getSovereigntyStructures: ', $e->getMessage(), PHP_EOL;
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

[**\Swagger\Client\Model\GetSovereigntyStructures200Ok[]**](../Model/GetSovereigntyStructures200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

