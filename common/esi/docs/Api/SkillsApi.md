# Swagger\Client\SkillsApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdSkillqueue**](SkillsApi.md#getCharactersCharacterIdSkillqueue) | **GET** /characters/{character_id}/skillqueue/ | Get character&#39;s skill queue
[**getCharactersCharacterIdSkills**](SkillsApi.md#getCharactersCharacterIdSkills) | **GET** /characters/{character_id}/skills/ | Get character skills


# **getCharactersCharacterIdSkillqueue**
> \Swagger\Client\Model\GetCharactersCharacterIdSkillqueue200Ok[] getCharactersCharacterIdSkillqueue($character_id, $datasource, $token, $user_agent, $x_user_agent)

Get character's skill queue

List the configured skill queue for the given character  ---  Alternate route: `/v2/characters/{character_id}/skillqueue/`  Alternate route: `/legacy/characters/{character_id}/skillqueue/`  Alternate route: `/dev/characters/{character_id}/skillqueue/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\SkillsApi();
$character_id = 56; // int | Character id of the target character
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdSkillqueue($character_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SkillsApi->getCharactersCharacterIdSkillqueue: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| Character id of the target character |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdSkillqueue200Ok[]**](../Model/GetCharactersCharacterIdSkillqueue200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdSkills**
> \Swagger\Client\Model\GetCharactersCharacterIdSkillsOk getCharactersCharacterIdSkills($character_id, $datasource, $token, $user_agent, $x_user_agent)

Get character skills

List all trained skills for the given character  ---  Alternate route: `/v3/characters/{character_id}/skills/`  Alternate route: `/dev/characters/{character_id}/skills/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\SkillsApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdSkills($character_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SkillsApi->getCharactersCharacterIdSkills: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdSkillsOk**](../Model/GetCharactersCharacterIdSkillsOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

