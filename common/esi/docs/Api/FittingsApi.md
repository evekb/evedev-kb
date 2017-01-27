# Swagger\Client\FittingsApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteCharactersCharacterIdFittingsFittingId**](FittingsApi.md#deleteCharactersCharacterIdFittingsFittingId) | **DELETE** /characters/{character_id}/fittings/{fitting_id}/ | Delete fitting
[**getCharactersCharacterIdFittings**](FittingsApi.md#getCharactersCharacterIdFittings) | **GET** /characters/{character_id}/fittings/ | Get fittings
[**postCharactersCharacterIdFittings**](FittingsApi.md#postCharactersCharacterIdFittings) | **POST** /characters/{character_id}/fittings/ | Create fitting


# **deleteCharactersCharacterIdFittingsFittingId**
> deleteCharactersCharacterIdFittingsFittingId($character_id, $fitting_id, $datasource)

Delete fitting

Delete a fitting from a character  ---  Alternate route: `/v1/characters/{character_id}/fittings/{fitting_id}/`  Alternate route: `/legacy/characters/{character_id}/fittings/{fitting_id}/`  Alternate route: `/dev/characters/{character_id}/fittings/{fitting_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FittingsApi();
$character_id = 56; // int | ID for a character
$fitting_id = 56; // int | ID for a fitting of this character
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteCharactersCharacterIdFittingsFittingId($character_id, $fitting_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FittingsApi->deleteCharactersCharacterIdFittingsFittingId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **fitting_id** | **int**| ID for a fitting of this character |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdFittings**
> \Swagger\Client\Model\GetCharactersCharacterIdFittings200Ok[] getCharactersCharacterIdFittings($character_id, $datasource)

Get fittings

Return fittings of a character  ---  Alternate route: `/v1/characters/{character_id}/fittings/`  Alternate route: `/legacy/characters/{character_id}/fittings/`  Alternate route: `/dev/characters/{character_id}/fittings/`   ---  This route is cached for up to 300 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FittingsApi();
$character_id = 56; // int | ID for a character
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdFittings($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FittingsApi->getCharactersCharacterIdFittings: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdFittings200Ok[]**](../Model/GetCharactersCharacterIdFittings200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postCharactersCharacterIdFittings**
> \Swagger\Client\Model\PostCharactersCharacterIdFittingsCreated postCharactersCharacterIdFittings($character_id, $fitting, $datasource)

Create fitting

Save a new fitting for a character  ---  Alternate route: `/v1/characters/{character_id}/fittings/`  Alternate route: `/legacy/characters/{character_id}/fittings/`  Alternate route: `/dev/characters/{character_id}/fittings/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FittingsApi();
$character_id = 56; // int | ID for a character
$fitting = new \Swagger\Client\Model\PostCharactersCharacterIdFittingsFitting(); // \Swagger\Client\Model\PostCharactersCharacterIdFittingsFitting | Details about the new fitting
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postCharactersCharacterIdFittings($character_id, $fitting, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FittingsApi->postCharactersCharacterIdFittings: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **fitting** | [**\Swagger\Client\Model\PostCharactersCharacterIdFittingsFitting**](../Model/\Swagger\Client\Model\PostCharactersCharacterIdFittingsFitting.md)| Details about the new fitting | [optional]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\PostCharactersCharacterIdFittingsCreated**](../Model/PostCharactersCharacterIdFittingsCreated.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

