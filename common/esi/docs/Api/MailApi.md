# Swagger\Client\MailApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteCharactersCharacterIdMailLabelsLabelId**](MailApi.md#deleteCharactersCharacterIdMailLabelsLabelId) | **DELETE** /characters/{character_id}/mail/labels/{label_id}/ | Delete a mail label
[**deleteCharactersCharacterIdMailMailId**](MailApi.md#deleteCharactersCharacterIdMailMailId) | **DELETE** /characters/{character_id}/mail/{mail_id}/ | Delete a mail
[**getCharactersCharacterIdMail**](MailApi.md#getCharactersCharacterIdMail) | **GET** /characters/{character_id}/mail/ | Return mail headers
[**getCharactersCharacterIdMailLabels**](MailApi.md#getCharactersCharacterIdMailLabels) | **GET** /characters/{character_id}/mail/labels/ | Get mail labels and unread counts
[**getCharactersCharacterIdMailLists**](MailApi.md#getCharactersCharacterIdMailLists) | **GET** /characters/{character_id}/mail/lists/ | Return mailing list subscriptions
[**getCharactersCharacterIdMailMailId**](MailApi.md#getCharactersCharacterIdMailMailId) | **GET** /characters/{character_id}/mail/{mail_id}/ | Return a mail
[**postCharactersCharacterIdMail**](MailApi.md#postCharactersCharacterIdMail) | **POST** /characters/{character_id}/mail/ | Send a new mail
[**postCharactersCharacterIdMailLabels**](MailApi.md#postCharactersCharacterIdMailLabels) | **POST** /characters/{character_id}/mail/labels/ | Create a mail label
[**putCharactersCharacterIdMailMailId**](MailApi.md#putCharactersCharacterIdMailMailId) | **PUT** /characters/{character_id}/mail/{mail_id}/ | Update metadata about a mail


# **deleteCharactersCharacterIdMailLabelsLabelId**
> deleteCharactersCharacterIdMailLabelsLabelId($character_id, $label_id, $datasource)

Delete a mail label

Delete a mail label  ---  Alternate route: `/v1/characters/{character_id}/mail/labels/{label_id}/`  Alternate route: `/legacy/characters/{character_id}/mail/labels/{label_id}/`  Alternate route: `/dev/characters/{character_id}/mail/labels/{label_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$label_id = 56; // int | An EVE label id
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteCharactersCharacterIdMailLabelsLabelId($character_id, $label_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->deleteCharactersCharacterIdMailLabelsLabelId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **label_id** | **int**| An EVE label id |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteCharactersCharacterIdMailMailId**
> deleteCharactersCharacterIdMailMailId($character_id, $mail_id, $datasource)

Delete a mail

Delete a mail  ---  Alternate route: `/v1/characters/{character_id}/mail/{mail_id}/`  Alternate route: `/legacy/characters/{character_id}/mail/{mail_id}/`  Alternate route: `/dev/characters/{character_id}/mail/{mail_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$mail_id = 56; // int | An EVE mail ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteCharactersCharacterIdMailMailId($character_id, $mail_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->deleteCharactersCharacterIdMailMailId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **mail_id** | **int**| An EVE mail ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdMail**
> \Swagger\Client\Model\GetCharactersCharacterIdMail200Ok[] getCharactersCharacterIdMail($character_id, $labels, $last_mail_id, $datasource)

Return mail headers

Return the 50 most recent mail headers belonging to the character that match the query criteria. Queries can be filtered by label, and last_mail_id can be used to paginate backwards.  ---  Alternate route: `/v1/characters/{character_id}/mail/`  Alternate route: `/legacy/characters/{character_id}/mail/`  Alternate route: `/dev/characters/{character_id}/mail/`   ---  This route is cached for up to 30 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$labels = array(56); // int[] | Fetch only mails that match one or more of the given labels
$last_mail_id = 56; // int | List only mail with an ID lower than the given ID, if present
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdMail($character_id, $labels, $last_mail_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->getCharactersCharacterIdMail: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **labels** | [**int[]**](../Model/int.md)| Fetch only mails that match one or more of the given labels | [optional]
 **last_mail_id** | **int**| List only mail with an ID lower than the given ID, if present | [optional]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdMail200Ok[]**](../Model/GetCharactersCharacterIdMail200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdMailLabels**
> \Swagger\Client\Model\GetCharactersCharacterIdMailLabelsOk getCharactersCharacterIdMailLabels($character_id, $datasource)

Get mail labels and unread counts

Return a list of the users mail labels, unread counts for each label and a total unread count.  ---  Alternate route: `/v3/characters/{character_id}/mail/labels/`  Alternate route: `/dev/characters/{character_id}/mail/labels/`   ---  This route is cached for up to 30 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdMailLabels($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->getCharactersCharacterIdMailLabels: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdMailLabelsOk**](../Model/GetCharactersCharacterIdMailLabelsOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdMailLists**
> \Swagger\Client\Model\GetCharactersCharacterIdMailLists200Ok[] getCharactersCharacterIdMailLists($character_id, $datasource)

Return mailing list subscriptions

Return all mailing lists that the character is subscribed to   ---  Alternate route: `/v1/characters/{character_id}/mail/lists/`  Alternate route: `/legacy/characters/{character_id}/mail/lists/`  Alternate route: `/dev/characters/{character_id}/mail/lists/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdMailLists($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->getCharactersCharacterIdMailLists: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdMailLists200Ok[]**](../Model/GetCharactersCharacterIdMailLists200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdMailMailId**
> \Swagger\Client\Model\GetCharactersCharacterIdMailMailIdOk getCharactersCharacterIdMailMailId($character_id, $mail_id, $datasource)

Return a mail

Return the contents of an EVE mail  ---  Alternate route: `/v1/characters/{character_id}/mail/{mail_id}/`  Alternate route: `/legacy/characters/{character_id}/mail/{mail_id}/`  Alternate route: `/dev/characters/{character_id}/mail/{mail_id}/`   ---  This route is cached for up to 30 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$mail_id = 56; // int | An EVE mail ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdMailMailId($character_id, $mail_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->getCharactersCharacterIdMailMailId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **mail_id** | **int**| An EVE mail ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdMailMailIdOk**](../Model/GetCharactersCharacterIdMailMailIdOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postCharactersCharacterIdMail**
> int postCharactersCharacterIdMail($character_id, $mail, $datasource)

Send a new mail

Create and send a new mail  ---  Alternate route: `/v1/characters/{character_id}/mail/`  Alternate route: `/legacy/characters/{character_id}/mail/`  Alternate route: `/dev/characters/{character_id}/mail/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | The sender's character ID
$mail = new \Swagger\Client\Model\PostCharactersCharacterIdMailMail(); // \Swagger\Client\Model\PostCharactersCharacterIdMailMail | The mail to send
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postCharactersCharacterIdMail($character_id, $mail, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->postCharactersCharacterIdMail: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| The sender&#39;s character ID |
 **mail** | [**\Swagger\Client\Model\PostCharactersCharacterIdMailMail**](../Model/\Swagger\Client\Model\PostCharactersCharacterIdMailMail.md)| The mail to send |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int**

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postCharactersCharacterIdMailLabels**
> int postCharactersCharacterIdMailLabels($character_id, $label, $datasource)

Create a mail label

Create a mail label  ---  Alternate route: `/v2/characters/{character_id}/mail/labels/`  Alternate route: `/legacy/characters/{character_id}/mail/labels/`  Alternate route: `/dev/characters/{character_id}/mail/labels/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$label = new \Swagger\Client\Model\PostCharactersCharacterIdMailLabelsLabel(); // \Swagger\Client\Model\PostCharactersCharacterIdMailLabelsLabel | Label to create
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postCharactersCharacterIdMailLabels($character_id, $label, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->postCharactersCharacterIdMailLabels: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **label** | [**\Swagger\Client\Model\PostCharactersCharacterIdMailLabelsLabel**](../Model/\Swagger\Client\Model\PostCharactersCharacterIdMailLabelsLabel.md)| Label to create | [optional]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int**

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putCharactersCharacterIdMailMailId**
> putCharactersCharacterIdMailMailId($character_id, $mail_id, $contents, $datasource)

Update metadata about a mail

Update metadata about a mail  ---  Alternate route: `/v1/characters/{character_id}/mail/{mail_id}/`  Alternate route: `/legacy/characters/{character_id}/mail/{mail_id}/`  Alternate route: `/dev/characters/{character_id}/mail/{mail_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MailApi();
$character_id = 56; // int | An EVE character ID
$mail_id = 56; // int | An EVE mail ID
$contents = new \Swagger\Client\Model\PutCharactersCharacterIdMailMailIdContents(); // \Swagger\Client\Model\PutCharactersCharacterIdMailMailIdContents | Data used to update the mail
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putCharactersCharacterIdMailMailId($character_id, $mail_id, $contents, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling MailApi->putCharactersCharacterIdMailMailId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **mail_id** | **int**| An EVE mail ID |
 **contents** | [**\Swagger\Client\Model\PutCharactersCharacterIdMailMailIdContents**](../Model/\Swagger\Client\Model\PutCharactersCharacterIdMailMailIdContents.md)| Data used to update the mail |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

