# Swagger\Client\BookmarksApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdBookmarks**](BookmarksApi.md#getCharactersCharacterIdBookmarks) | **GET** /characters/{character_id}/bookmarks/ | List bookmarks
[**getCharactersCharacterIdBookmarksFolders**](BookmarksApi.md#getCharactersCharacterIdBookmarksFolders) | **GET** /characters/{character_id}/bookmarks/folders/ | List bookmark folders


# **getCharactersCharacterIdBookmarks**
> \Swagger\Client\Model\GetCharactersCharacterIdBookmarks200Ok[] getCharactersCharacterIdBookmarks($character_id, $datasource)

List bookmarks

List your character's personal bookmarks  ---  Alternate route: `/v1/characters/{character_id}/bookmarks/`  Alternate route: `/legacy/characters/{character_id}/bookmarks/`  Alternate route: `/dev/characters/{character_id}/bookmarks/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\BookmarksApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdBookmarks($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BookmarksApi->getCharactersCharacterIdBookmarks: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdBookmarks200Ok[]**](../Model/GetCharactersCharacterIdBookmarks200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdBookmarksFolders**
> \Swagger\Client\Model\GetCharactersCharacterIdBookmarksFolders200Ok[] getCharactersCharacterIdBookmarksFolders($character_id, $datasource)

List bookmark folders

List your character's personal bookmark folders  ---  Alternate route: `/v1/characters/{character_id}/bookmarks/folders/`  Alternate route: `/legacy/characters/{character_id}/bookmarks/folders/`  Alternate route: `/dev/characters/{character_id}/bookmarks/folders/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\BookmarksApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdBookmarksFolders($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling BookmarksApi->getCharactersCharacterIdBookmarksFolders: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdBookmarksFolders200Ok[]**](../Model/GetCharactersCharacterIdBookmarksFolders200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

