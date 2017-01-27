# Swagger\Client\ContactsApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteCharactersCharacterIdContacts**](ContactsApi.md#deleteCharactersCharacterIdContacts) | **DELETE** /characters/{character_id}/contacts/ | Delete contacts
[**getCharactersCharacterIdContacts**](ContactsApi.md#getCharactersCharacterIdContacts) | **GET** /characters/{character_id}/contacts/ | Get contacts
[**getCharactersCharacterIdContactsLabels**](ContactsApi.md#getCharactersCharacterIdContactsLabels) | **GET** /characters/{character_id}/contacts/labels/ | Get contact labels
[**postCharactersCharacterIdContacts**](ContactsApi.md#postCharactersCharacterIdContacts) | **POST** /characters/{character_id}/contacts/ | Add contacts
[**putCharactersCharacterIdContacts**](ContactsApi.md#putCharactersCharacterIdContacts) | **PUT** /characters/{character_id}/contacts/ | Edit contacts


# **deleteCharactersCharacterIdContacts**
> deleteCharactersCharacterIdContacts($character_id, $contact_ids, $datasource)

Delete contacts

Bulk delete contacts  ---  Alternate route: `/v1/characters/{character_id}/contacts/`  Alternate route: `/legacy/characters/{character_id}/contacts/`  Alternate route: `/dev/characters/{character_id}/contacts/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\ContactsApi();
$character_id = 56; // int | ID for a character
$contact_ids = array(new int[]()); // int[] | A list of contacts to edit
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteCharactersCharacterIdContacts($character_id, $contact_ids, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->deleteCharactersCharacterIdContacts: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **contact_ids** | **int[]**| A list of contacts to edit |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdContacts**
> \Swagger\Client\Model\GetCharactersCharacterIdContacts200Ok[] getCharactersCharacterIdContacts($character_id, $page, $datasource)

Get contacts

Return contacts of a character  ---  Alternate route: `/v1/characters/{character_id}/contacts/`  Alternate route: `/legacy/characters/{character_id}/contacts/`  Alternate route: `/dev/characters/{character_id}/contacts/`   ---  This route is cached for up to 300 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\ContactsApi();
$character_id = 56; // int | ID for a character
$page = 1; // int | page integer
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdContacts($character_id, $page, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->getCharactersCharacterIdContacts: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **page** | **int**| page integer | [optional] [default to 1]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdContacts200Ok[]**](../Model/GetCharactersCharacterIdContacts200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdContactsLabels**
> \Swagger\Client\Model\GetCharactersCharacterIdContactsLabels200Ok[] getCharactersCharacterIdContactsLabels($character_id, $datasource)

Get contact labels

Return custom labels for contacts the character defined  ---  Alternate route: `/v1/characters/{character_id}/contacts/labels/`  Alternate route: `/legacy/characters/{character_id}/contacts/labels/`  Alternate route: `/dev/characters/{character_id}/contacts/labels/`   ---  This route is cached for up to 300 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\ContactsApi();
$character_id = 56; // int | ID for a character
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdContactsLabels($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->getCharactersCharacterIdContactsLabels: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdContactsLabels200Ok[]**](../Model/GetCharactersCharacterIdContactsLabels200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postCharactersCharacterIdContacts**
> int[] postCharactersCharacterIdContacts($character_id, $standing, $contact_ids, $watched, $label_id, $datasource)

Add contacts

Bulk add contacts with same settings  ---  Alternate route: `/v1/characters/{character_id}/contacts/`  Alternate route: `/legacy/characters/{character_id}/contacts/`  Alternate route: `/dev/characters/{character_id}/contacts/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\ContactsApi();
$character_id = 56; // int | ID for a character
$standing = 3.4; // float | Standing for the new contact
$contact_ids = array(new int[]()); // int[] | A list of contacts to add
$watched = false; // bool | Whether the new contact should be watched, note this is only effective on characters
$label_id = 0; // int | Add a custom label to the new contact
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postCharactersCharacterIdContacts($character_id, $standing, $contact_ids, $watched, $label_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->postCharactersCharacterIdContacts: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **standing** | **float**| Standing for the new contact |
 **contact_ids** | **int[]**| A list of contacts to add |
 **watched** | **bool**| Whether the new contact should be watched, note this is only effective on characters | [optional] [default to false]
 **label_id** | **int**| Add a custom label to the new contact | [optional] [default to 0]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int[]**

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putCharactersCharacterIdContacts**
> putCharactersCharacterIdContacts($character_id, $standing, $contact_ids, $watched, $label_id, $datasource)

Edit contacts

Bulk edit contacts with same settings  ---  Alternate route: `/v1/characters/{character_id}/contacts/`  Alternate route: `/legacy/characters/{character_id}/contacts/`  Alternate route: `/dev/characters/{character_id}/contacts/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\ContactsApi();
$character_id = 56; // int | ID for a character
$standing = 3.4; // float | Standing for the contact
$contact_ids = array(new int[]()); // int[] | A list of contacts to edit
$watched = false; // bool | Whether the contact should be watched, note this is only effective on characters
$label_id = 0; // int | Add a custom label to the contact, use 0 for clearing label
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putCharactersCharacterIdContacts($character_id, $standing, $contact_ids, $watched, $label_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->putCharactersCharacterIdContacts: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **standing** | **float**| Standing for the contact |
 **contact_ids** | **int[]**| A list of contacts to edit |
 **watched** | **bool**| Whether the contact should be watched, note this is only effective on characters | [optional] [default to false]
 **label_id** | **int**| Add a custom label to the contact, use 0 for clearing label | [optional] [default to 0]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

