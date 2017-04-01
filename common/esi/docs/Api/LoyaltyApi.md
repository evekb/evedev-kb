# Swagger\Client\LoyaltyApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdLoyaltyPoints**](LoyaltyApi.md#getCharactersCharacterIdLoyaltyPoints) | **GET** /characters/{character_id}/loyalty/points/ | Get loyalty points
[**getLoyaltyStoresCorporationIdOffers**](LoyaltyApi.md#getLoyaltyStoresCorporationIdOffers) | **GET** /loyalty/stores/{corporation_id}/offers/ | List loyalty store offers


# **getCharactersCharacterIdLoyaltyPoints**
> \Swagger\Client\Model\GetCharactersCharacterIdLoyaltyPoints200Ok[] getCharactersCharacterIdLoyaltyPoints($character_id, $datasource, $token, $user_agent, $x_user_agent)

Get loyalty points

Return a list of loyalty points for all corporations the character has worked for  ---  Alternate route: `/v1/characters/{character_id}/loyalty/points/`  Alternate route: `/legacy/characters/{character_id}/loyalty/points/`  Alternate route: `/dev/characters/{character_id}/loyalty/points/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\LoyaltyApi();
$character_id = 56; // int | ID for a character
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdLoyaltyPoints($character_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LoyaltyApi->getCharactersCharacterIdLoyaltyPoints: ', $e->getMessage(), PHP_EOL;
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

[**\Swagger\Client\Model\GetCharactersCharacterIdLoyaltyPoints200Ok[]**](../Model/GetCharactersCharacterIdLoyaltyPoints200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getLoyaltyStoresCorporationIdOffers**
> \Swagger\Client\Model\GetLoyaltyStoresCorporationIdOffers200Ok[] getLoyaltyStoresCorporationIdOffers($corporation_id, $datasource, $user_agent, $x_user_agent)

List loyalty store offers

Return a list of offers from a specific corporation's loyalty store  ---  Alternate route: `/v1/loyalty/stores/{corporation_id}/offers/`  Alternate route: `/legacy/loyalty/stores/{corporation_id}/offers/`  Alternate route: `/dev/loyalty/stores/{corporation_id}/offers/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\LoyaltyApi();
$corporation_id = 56; // int | ID of a corporation
$datasource = "tranquility"; // string | The server name you would like data from
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getLoyaltyStoresCorporationIdOffers($corporation_id, $datasource, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling LoyaltyApi->getLoyaltyStoresCorporationIdOffers: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| ID of a corporation |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetLoyaltyStoresCorporationIdOffers200Ok[]**](../Model/GetLoyaltyStoresCorporationIdOffers200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

