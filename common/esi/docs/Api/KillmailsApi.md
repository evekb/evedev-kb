# Swagger\Client\KillmailsApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdKillmailsRecent**](KillmailsApi.md#getCharactersCharacterIdKillmailsRecent) | **GET** /characters/{character_id}/killmails/recent/ | List kills and losses
[**getKillmailsKillmailIdKillmailHash**](KillmailsApi.md#getKillmailsKillmailIdKillmailHash) | **GET** /killmails/{killmail_id}/{killmail_hash}/ | Get a single killmail


# **getCharactersCharacterIdKillmailsRecent**
> \Swagger\Client\Model\GetCharactersCharacterIdKillmailsRecent200Ok[] getCharactersCharacterIdKillmailsRecent($character_id, $datasource, $max_count, $max_kill_id, $token, $user_agent, $x_user_agent)

List kills and losses

Return a list of character's recent kills and losses  ---  Alternate route: `/v1/characters/{character_id}/killmails/recent/`  Alternate route: `/legacy/characters/{character_id}/killmails/recent/`  Alternate route: `/dev/characters/{character_id}/killmails/recent/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\KillmailsApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$max_count = 50; // int | How many killmails to return at maximum
$max_kill_id = 56; // int | Only return killmails with ID smaller than this.
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdKillmailsRecent($character_id, $datasource, $max_count, $max_kill_id, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling KillmailsApi->getCharactersCharacterIdKillmailsRecent: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **max_count** | **int**| How many killmails to return at maximum | [optional] [default to 50]
 **max_kill_id** | **int**| Only return killmails with ID smaller than this. | [optional]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdKillmailsRecent200Ok[]**](../Model/GetCharactersCharacterIdKillmailsRecent200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getKillmailsKillmailIdKillmailHash**
> \Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOk getKillmailsKillmailIdKillmailHash($killmail_hash, $killmail_id, $datasource, $user_agent, $x_user_agent)

Get a single killmail

Return a single killmail from its ID and hash  ---  Alternate route: `/v1/killmails/{killmail_id}/{killmail_hash}/`  Alternate route: `/legacy/killmails/{killmail_id}/{killmail_hash}/`  Alternate route: `/dev/killmails/{killmail_id}/{killmail_hash}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\KillmailsApi();
$killmail_hash = "killmail_hash_example"; // string | The killmail hash for verification
$killmail_id = 56; // int | The killmail ID to be queried
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getKillmailsKillmailIdKillmailHash($killmail_hash, $killmail_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling KillmailsApi->getKillmailsKillmailIdKillmailHash: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **killmail_hash** | **string**| The killmail hash for verification |
 **killmail_id** | **int**| The killmail ID to be queried |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetKillmailsKillmailIdKillmailHashOk**](../Model/GetKillmailsKillmailIdKillmailHashOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

