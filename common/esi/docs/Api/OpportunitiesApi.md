# Swagger\Client\OpportunitiesApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdOpportunities**](OpportunitiesApi.md#getCharactersCharacterIdOpportunities) | **GET** /characters/{character_id}/opportunities/ | Get a character&#39;s completed tasks
[**getOpportunitiesGroups**](OpportunitiesApi.md#getOpportunitiesGroups) | **GET** /opportunities/groups/ | Get opportunities groups
[**getOpportunitiesGroupsGroupId**](OpportunitiesApi.md#getOpportunitiesGroupsGroupId) | **GET** /opportunities/groups/{group_id}/ | Get opportunities group
[**getOpportunitiesTasks**](OpportunitiesApi.md#getOpportunitiesTasks) | **GET** /opportunities/tasks/ | Get opportunities tasks
[**getOpportunitiesTasksTaskId**](OpportunitiesApi.md#getOpportunitiesTasksTaskId) | **GET** /opportunities/tasks/{task_id}/ | Get opportunities task


# **getCharactersCharacterIdOpportunities**
> \Swagger\Client\Model\GetCharactersCharacterIdOpportunities200Ok[] getCharactersCharacterIdOpportunities($character_id, $datasource, $token, $user_agent, $x_user_agent)

Get a character's completed tasks

Return a list of tasks finished by a character  ---  Alternate route: `/v1/characters/{character_id}/opportunities/`  Alternate route: `/legacy/characters/{character_id}/opportunities/`  Alternate route: `/dev/characters/{character_id}/opportunities/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\OpportunitiesApi();
$character_id = 56; // int | ID for a character
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdOpportunities($character_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OpportunitiesApi->getCharactersCharacterIdOpportunities: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdOpportunities200Ok[]**](../Model/GetCharactersCharacterIdOpportunities200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getOpportunitiesGroups**
> int[] getOpportunitiesGroups($datasource, $user_agent, $x_user_agent)

Get opportunities groups

Return a list of opportunities groups  ---  Alternate route: `/v1/opportunities/groups/`  Alternate route: `/legacy/opportunities/groups/`  Alternate route: `/dev/opportunities/groups/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\OpportunitiesApi();
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getOpportunitiesGroups($datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OpportunitiesApi->getOpportunitiesGroups: ', $e->getMessage(), PHP_EOL;
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

# **getOpportunitiesGroupsGroupId**
> \Swagger\Client\Model\GetOpportunitiesGroupsGroupIdOk getOpportunitiesGroupsGroupId($group_id, $datasource, $language, $user_agent, $x_user_agent)

Get opportunities group

Return information of an opportunities group  ---  Alternate route: `/v1/opportunities/groups/{group_id}/`  Alternate route: `/legacy/opportunities/groups/{group_id}/`  Alternate route: `/dev/opportunities/groups/{group_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\OpportunitiesApi();
$group_id = 56; // int | ID of an opportunities group
$datasource = "tranquility"; // string | The server name you would like data from
$language = "en-us"; // string | Language to use in the response
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getOpportunitiesGroupsGroupId($group_id, $datasource, $language, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OpportunitiesApi->getOpportunitiesGroupsGroupId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **group_id** | **int**| ID of an opportunities group |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetOpportunitiesGroupsGroupIdOk**](../Model/GetOpportunitiesGroupsGroupIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getOpportunitiesTasks**
> int[] getOpportunitiesTasks($datasource, $user_agent, $x_user_agent)

Get opportunities tasks

Return a list of opportunities tasks  ---  Alternate route: `/v1/opportunities/tasks/`  Alternate route: `/legacy/opportunities/tasks/`  Alternate route: `/dev/opportunities/tasks/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\OpportunitiesApi();
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getOpportunitiesTasks($datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OpportunitiesApi->getOpportunitiesTasks: ', $e->getMessage(), PHP_EOL;
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

# **getOpportunitiesTasksTaskId**
> \Swagger\Client\Model\GetOpportunitiesTasksTaskIdOk getOpportunitiesTasksTaskId($task_id, $datasource, $user_agent, $x_user_agent)

Get opportunities task

Return information of an opportunities task  ---  Alternate route: `/v1/opportunities/tasks/{task_id}/`  Alternate route: `/legacy/opportunities/tasks/{task_id}/`  Alternate route: `/dev/opportunities/tasks/{task_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\OpportunitiesApi();
$task_id = 56; // int | ID of an opportunities task
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getOpportunitiesTasksTaskId($task_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OpportunitiesApi->getOpportunitiesTasksTaskId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **task_id** | **int**| ID of an opportunities task |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetOpportunitiesTasksTaskIdOk**](../Model/GetOpportunitiesTasksTaskIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

