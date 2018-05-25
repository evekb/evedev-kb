# Swagger\Client\SearchApi

All URIs are relative to *https://esi.evetech.net*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdSearch**](SearchApi.md#getCharactersCharacterIdSearch) | **GET** /v3/characters/{character_id}/search/ | Search on a string
[**getSearch**](SearchApi.md#getSearch) | **GET** /v2/search/ | Search on a string


# **getCharactersCharacterIdSearch**
> \Swagger\Client\Model\GetCharactersCharacterIdSearchOk getCharactersCharacterIdSearch($categories, $character_id, $search, $accept_language, $datasource, $if_none_match, $language, $strict, $token)

Search on a string

Search for entities that match a given sub-string.  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\SearchApi();
$categories = array("categories_example"); // string[] | Type of entities to search for
$character_id = 56; // int | An EVE character ID
$search = "search_example"; // string | The string to search on
$accept_language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$language = "en-us"; // string | Language to use in the response, takes precedence over Accept-Language
$strict = false; // bool | Whether the search should be a strict match
$token = "token_example"; // string | Access token to use if unable to set a header

try {
    $result = $api_instance->getCharactersCharacterIdSearch($categories, $character_id, $search, $accept_language, $datasource, $if_none_match, $language, $strict, $token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->getCharactersCharacterIdSearch: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **categories** | [**string[]**](../Model/string.md)| Type of entities to search for |
 **character_id** | **int**| An EVE character ID |
 **search** | **string**| The string to search on |
 **accept_language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **language** | **string**| Language to use in the response, takes precedence over Accept-Language | [optional] [default to en-us]
 **strict** | **bool**| Whether the search should be a strict match | [optional] [default to false]
 **token** | **string**| Access token to use if unable to set a header | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdSearchOk**](../Model/GetCharactersCharacterIdSearchOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getSearch**
> \Swagger\Client\Model\GetSearchOk getSearch($categories, $search, $accept_language, $datasource, $if_none_match, $language, $strict)

Search on a string

Search for entities that match a given sub-string.  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\SearchApi();
$categories = array("categories_example"); // string[] | Type of entities to search for
$search = "search_example"; // string | The string to search on
$accept_language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$language = "en-us"; // string | Language to use in the response, takes precedence over Accept-Language
$strict = false; // bool | Whether the search should be a strict match

try {
    $result = $api_instance->getSearch($categories, $search, $accept_language, $datasource, $if_none_match, $language, $strict);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling SearchApi->getSearch: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **categories** | [**string[]**](../Model/string.md)| Type of entities to search for |
 **search** | **string**| The string to search on |
 **accept_language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **language** | **string**| Language to use in the response, takes precedence over Accept-Language | [optional] [default to en-us]
 **strict** | **bool**| Whether the search should be a strict match | [optional] [default to false]

### Return type

[**\Swagger\Client\Model\GetSearchOk**](../Model/GetSearchOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

