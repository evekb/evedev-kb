# Swagger\Client\ClonesApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdClones**](ClonesApi.md#getCharactersCharacterIdClones) | **GET** /characters/{character_id}/clones/ | Get clones


# **getCharactersCharacterIdClones**
> \Swagger\Client\Model\GetCharactersCharacterIdClonesOk getCharactersCharacterIdClones($character_id, $datasource, $token, $user_agent, $x_user_agent)

Get clones

A list of the character's clones  ---  Alternate route: `/v2/characters/{character_id}/clones/`  Alternate route: `/dev/characters/{character_id}/clones/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\ClonesApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdClones($character_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ClonesApi->getCharactersCharacterIdClones: ', $e->getMessage(), PHP_EOL;
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

[**\Swagger\Client\Model\GetCharactersCharacterIdClonesOk**](../Model/GetCharactersCharacterIdClonesOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

