# Swagger\Client\UserInterfaceApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**postUiAutopilotWaypoint**](UserInterfaceApi.md#postUiAutopilotWaypoint) | **POST** /ui/autopilot/waypoint/ | Set Autopilot Waypoint
[**postUiOpenwindowContract**](UserInterfaceApi.md#postUiOpenwindowContract) | **POST** /ui/openwindow/contract/ | Open Contract Window
[**postUiOpenwindowInformation**](UserInterfaceApi.md#postUiOpenwindowInformation) | **POST** /ui/openwindow/information/ | Open Information Window
[**postUiOpenwindowMarketdetails**](UserInterfaceApi.md#postUiOpenwindowMarketdetails) | **POST** /ui/openwindow/marketdetails/ | Open Market Details
[**postUiOpenwindowNewmail**](UserInterfaceApi.md#postUiOpenwindowNewmail) | **POST** /ui/openwindow/newmail/ | Open New Mail Window


# **postUiAutopilotWaypoint**
> postUiAutopilotWaypoint($destination_id, $clear_other_waypoints, $add_to_beginning, $datasource)

Set Autopilot Waypoint

Set a solar system as autopilot waypoint  ---  Alternate route: `/v2/ui/autopilot/waypoint/`  Alternate route: `/dev/ui/autopilot/waypoint/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\UserInterfaceApi();
$destination_id = 789; // int | The destination to travel to, can be solar system, station or structure's id
$clear_other_waypoints = false; // bool | Whether clean other waypoints beforing adding this one
$add_to_beginning = false; // bool | Whether this solar system should be added to the beginning of all waypoints
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->postUiAutopilotWaypoint($destination_id, $clear_other_waypoints, $add_to_beginning, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling UserInterfaceApi->postUiAutopilotWaypoint: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **destination_id** | **int**| The destination to travel to, can be solar system, station or structure&#39;s id |
 **clear_other_waypoints** | **bool**| Whether clean other waypoints beforing adding this one | [default to false]
 **add_to_beginning** | **bool**| Whether this solar system should be added to the beginning of all waypoints | [default to false]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postUiOpenwindowContract**
> postUiOpenwindowContract($contract_id, $datasource)

Open Contract Window

Open the contract window inside the client  ---  Alternate route: `/v1/ui/openwindow/contract/`  Alternate route: `/legacy/ui/openwindow/contract/`  Alternate route: `/dev/ui/openwindow/contract/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\UserInterfaceApi();
$contract_id = 56; // int | The contract to open
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->postUiOpenwindowContract($contract_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling UserInterfaceApi->postUiOpenwindowContract: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **contract_id** | **int**| The contract to open |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postUiOpenwindowInformation**
> postUiOpenwindowInformation($target_id, $datasource)

Open Information Window

Open the information window for a character, corporation or alliance inside the client  ---  Alternate route: `/v1/ui/openwindow/information/`  Alternate route: `/legacy/ui/openwindow/information/`  Alternate route: `/dev/ui/openwindow/information/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\UserInterfaceApi();
$target_id = 56; // int | The target to open
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->postUiOpenwindowInformation($target_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling UserInterfaceApi->postUiOpenwindowInformation: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **target_id** | **int**| The target to open |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postUiOpenwindowMarketdetails**
> postUiOpenwindowMarketdetails($type_id, $datasource)

Open Market Details

Open the market details window for a specific typeID inside the client  ---  Alternate route: `/v1/ui/openwindow/marketdetails/`  Alternate route: `/legacy/ui/openwindow/marketdetails/`  Alternate route: `/dev/ui/openwindow/marketdetails/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\UserInterfaceApi();
$type_id = 56; // int | The item type to open in market window
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->postUiOpenwindowMarketdetails($type_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling UserInterfaceApi->postUiOpenwindowMarketdetails: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **type_id** | **int**| The item type to open in market window |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postUiOpenwindowNewmail**
> postUiOpenwindowNewmail($new_mail, $datasource)

Open New Mail Window

Open the New Mail window, according to settings from the request if applicable  ---  Alternate route: `/v1/ui/openwindow/newmail/`  Alternate route: `/legacy/ui/openwindow/newmail/`  Alternate route: `/dev/ui/openwindow/newmail/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\UserInterfaceApi();
$new_mail = new \Swagger\Client\Model\PostUiOpenwindowNewmailNewMail(); // \Swagger\Client\Model\PostUiOpenwindowNewmailNewMail | The details of mail to create
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->postUiOpenwindowNewmail($new_mail, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling UserInterfaceApi->postUiOpenwindowNewmail: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **new_mail** | [**\Swagger\Client\Model\PostUiOpenwindowNewmailNewMail**](../Model/\Swagger\Client\Model\PostUiOpenwindowNewmailNewMail.md)| The details of mail to create |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

