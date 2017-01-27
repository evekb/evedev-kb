# Swagger\Client\WalletApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdWallets**](WalletApi.md#getCharactersCharacterIdWallets) | **GET** /characters/{character_id}/wallets/ | List wallets and balances


# **getCharactersCharacterIdWallets**
> \Swagger\Client\Model\GetCharactersCharacterIdWallets200Ok[] getCharactersCharacterIdWallets($character_id, $datasource)

List wallets and balances

List your wallets and their balances. Characters typically have only one wallet, with wallet_id 1000 being the master wallet.  ---  Alternate route: `/v1/characters/{character_id}/wallets/`  Alternate route: `/legacy/characters/{character_id}/wallets/`  Alternate route: `/dev/characters/{character_id}/wallets/`   ---  This route is cached for up to 120 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\WalletApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdWallets($character_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling WalletApi->getCharactersCharacterIdWallets: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdWallets200Ok[]**](../Model/GetCharactersCharacterIdWallets200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

