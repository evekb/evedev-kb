# Swagger\Client\WalletApi

All URIs are relative to *https://esi.tech.ccp.is/*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdWallet**](WalletApi.md#getCharactersCharacterIdWallet) | **GET** /v1/characters/{character_id}/wallet/ | Get a character&#39;s wallet balance
[**getCharactersCharacterIdWalletJournal**](WalletApi.md#getCharactersCharacterIdWalletJournal) | **GET** /v1/characters/{character_id}/wallet/journal/ | Get character wallet journal
[**getCharactersCharacterIdWalletTransactions**](WalletApi.md#getCharactersCharacterIdWalletTransactions) | **GET** /v1/characters/{character_id}/wallet/transactions/ | Get wallet transactions
[**getCorporationsCorporationIdWallets**](WalletApi.md#getCorporationsCorporationIdWallets) | **GET** /v1/corporations/{corporation_id}/wallets/ | Returns a corporation&#39;s wallet balance
[**getCorporationsCorporationIdWalletsDivisionJournal**](WalletApi.md#getCorporationsCorporationIdWalletsDivisionJournal) | **GET** /v1/corporations/{corporation_id}/wallets/{division}/journal/ | Get corporation wallet journal
[**getCorporationsCorporationIdWalletsDivisionTransactions**](WalletApi.md#getCorporationsCorporationIdWalletsDivisionTransactions) | **GET** /v1/corporations/{corporation_id}/wallets/{division}/transactions/ | Get corporation wallet transactions


# **getCharactersCharacterIdWallet**
> float getCharactersCharacterIdWallet($character_id, $datasource, $token, $user_agent, $x_user_agent)

Get a character's wallet balance

Returns a character's wallet balance  ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use if unable to set a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdWallet($character_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCharactersCharacterIdWallet: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use if unable to set a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

**float**

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdWalletJournal**
> \Swagger\Client\Model\GetCharactersCharacterIdWalletJournal200Ok[] getCharactersCharacterIdWalletJournal($character_id, $datasource, $from_id, $token, $user_agent, $x_user_agent)

Get character wallet journal

Retrieve character wallet journal  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$from_id = 789; // int | Only show journal entries happened before the transaction referenced by this id
$token = "token_example"; // string | Access token to use if unable to set a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdWalletJournal($character_id, $datasource, $from_id, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCharactersCharacterIdWalletJournal: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **from_id** | **int**| Only show journal entries happened before the transaction referenced by this id | [optional]
 **token** | **string**| Access token to use if unable to set a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdWalletJournal200Ok[]**](../Model/GetCharactersCharacterIdWalletJournal200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdWalletTransactions**
> \Swagger\Client\Model\GetCharactersCharacterIdWalletTransactions200Ok[] getCharactersCharacterIdWalletTransactions($character_id, $datasource, $from_id, $token, $user_agent, $x_user_agent)

Get wallet transactions

Get wallet transactions of a character  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$from_id = 789; // int | Only show transactions happened before the one referenced by this id
$token = "token_example"; // string | Access token to use if unable to set a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCharactersCharacterIdWalletTransactions($character_id, $datasource, $from_id, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCharactersCharacterIdWalletTransactions: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **from_id** | **int**| Only show transactions happened before the one referenced by this id | [optional]
 **token** | **string**| Access token to use if unable to set a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdWalletTransactions200Ok[]**](../Model/GetCharactersCharacterIdWalletTransactions200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdWallets**
> \Swagger\Client\Model\GetCorporationsCorporationIdWallets200Ok[] getCorporationsCorporationIdWallets($corporation_id, $datasource, $token, $user_agent, $x_user_agent)

Returns a corporation's wallet balance

Get a corporation's wallets  ---  This route is cached for up to 300 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$corporation_id = 56; // int | An EVE corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$token = "token_example"; // string | Access token to use if unable to set a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdWallets($corporation_id, $datasource, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCorporationsCorporationIdWallets: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **token** | **string**| Access token to use if unable to set a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdWallets200Ok[]**](../Model/GetCorporationsCorporationIdWallets200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdWalletsDivisionJournal**
> \Swagger\Client\Model\GetCorporationsCorporationIdWalletsDivisionJournal200Ok[] getCorporationsCorporationIdWalletsDivisionJournal($corporation_id, $division, $datasource, $from_id, $token, $user_agent, $x_user_agent)

Get corporation wallet journal

Retrieve corporation wallet journal  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$corporation_id = 56; // int | An EVE corporation ID
$division = 56; // int | Wallet key of the division to fetch journals from
$datasource = "tranquility"; // string | The server name you would like data from
$from_id = 789; // int | Only show journal entries happened before the transaction referenced by this id
$token = "token_example"; // string | Access token to use if unable to set a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdWalletsDivisionJournal($corporation_id, $division, $datasource, $from_id, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCorporationsCorporationIdWalletsDivisionJournal: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **division** | **int**| Wallet key of the division to fetch journals from |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **from_id** | **int**| Only show journal entries happened before the transaction referenced by this id | [optional]
 **token** | **string**| Access token to use if unable to set a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdWalletsDivisionJournal200Ok[]**](../Model/GetCorporationsCorporationIdWalletsDivisionJournal200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdWalletsDivisionTransactions**
> \Swagger\Client\Model\GetCorporationsCorporationIdWalletsDivisionTransactions200Ok[] getCorporationsCorporationIdWalletsDivisionTransactions($corporation_id, $division, $datasource, $from_id, $token, $user_agent, $x_user_agent)

Get corporation wallet transactions

Get wallet transactions of a corporation  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$corporation_id = 56; // int | An EVE corporation ID
$division = 56; // int | Wallet key of the division to fetch journals from
$datasource = "tranquility"; // string | The server name you would like data from
$from_id = 789; // int | Only show journal entries happened before the transaction referenced by this id
$token = "token_example"; // string | Access token to use if unable to set a header
$user_agent = "user_agent_example"; // string | Client identifier, takes precedence over headers
$x_user_agent = "x_user_agent_example"; // string | Client identifier, takes precedence over User-Agent

try {
    $result = $api_instance->getCorporationsCorporationIdWalletsDivisionTransactions($corporation_id, $division, $datasource, $from_id, $token, $user_agent, $x_user_agent);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCorporationsCorporationIdWalletsDivisionTransactions: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **division** | **int**| Wallet key of the division to fetch journals from |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **from_id** | **int**| Only show journal entries happened before the transaction referenced by this id | [optional]
 **token** | **string**| Access token to use if unable to set a header | [optional]
 **user_agent** | **string**| Client identifier, takes precedence over headers | [optional]
 **x_user_agent** | **string**| Client identifier, takes precedence over User-Agent | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdWalletsDivisionTransactions200Ok[]**](../Model/GetCorporationsCorporationIdWalletsDivisionTransactions200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

