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
> deleteCharactersCharacterIdContacts($character_id, $contact_ids, $datasource, $token, $user_agent, $x_user_agent)

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
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $api_instance->deleteCharactersCharacterIdContacts($character_id, $contact_ids, $datasource, $token, $user_agent, $x_user_agent);
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
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdContacts**
> \Swagger\Client\Model\GetCharactersCharacterIdContacts200Ok[] getCharactersCharacterIdContacts($character_id, $datasource, $page, $token, $user_agent, $x_user_agent)

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
$datasource = "tranquility"; // string | The server name you would like data from
$page = 1; // int | page integer
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdContacts($character_id, $datasource, $page, $token, $user_agent, $x_user_agent);
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
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **page** | **int**| page integer | [optional] [default to 1]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdContacts200Ok[]**](../Model/GetCharactersCharacterIdContacts200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdContactsLabels**
> \Swagger\Client\Model\GetCharactersCharacterIdContactsLabels200Ok[] getCharactersCharacterIdContactsLabels($character_id, $datasource, $token, $user_agent, $x_user_agent)

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
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdContactsLabels($character_id, $datasource, $token, $user_agent, $x_user_agent);
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
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdContactsLabels200Ok[]**](../Model/GetCharactersCharacterIdContactsLabels200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postCharactersCharacterIdContacts**
> int[] postCharactersCharacterIdContacts($character_id, $contact_ids, $standing, $datasource, $label_id, $token, $user_agent, $watched, $x_user_agent)

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
$contact_ids = array(new int[]()); // int[] | A list of contacts to add
$standing = 3.4; // float | Standing for the new contact
$datasource = "tranquility"; // string | The server name you would like data from
$label_id = 0; // int | Add a custom label to the new contact
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$watched = false; // bool | Whether the new contact should be watched, note this is only effective on characters
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->postCharactersCharacterIdContacts($character_id, $contact_ids, $standing, $datasource, $label_id, $token, $user_agent, $watched, $x_user_agent);
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
 **contact_ids** | **int[]**| A list of contacts to add |
 **standing** | **float**| Standing for the new contact |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **label_id** | **int**| Add a custom label to the new contact | [optional] [default to 0]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **watched** | **bool**| Whether the new contact should be watched, note this is only effective on characters | [optional] [default to false]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

**int[]**

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putCharactersCharacterIdContacts**
> putCharactersCharacterIdContacts($character_id, $contact_ids, $standing, $datasource, $label_id, $token, $user_agent, $watched, $x_user_agent)

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
$contact_ids = array(new int[]()); // int[] | A list of contacts to edit
$standing = 3.4; // float | Standing for the contact
$datasource = "tranquility"; // string | The server name you would like data from
$label_id = 0; // int | Add a custom label to the contact, use 0 for clearing label
$token = "token_example"; // string | Access token to use, if preferred over a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$watched = false; // bool | Whether the contact should be watched, note this is only effective on characters
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $api_instance->putCharactersCharacterIdContacts($character_id, $contact_ids, $standing, $datasource, $label_id, $token, $user_agent, $watched, $x_user_agent);
} catch (Exception $e) {
    echo 'Exception when calling ContactsApi->putCharactersCharacterIdContacts: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| ID for a character |
 **contact_ids** | **int[]**| A list of contacts to edit |
 **standing** | **float**| Standing for the contact |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **label_id** | **int**| Add a custom label to the contact, use 0 for clearing label | [optional] [default to 0]
 **token** | **string**| Access token to use, if preferred over a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **watched** | **bool**| Whether the contact should be watched, note this is only effective on characters | [optional] [default to false]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

