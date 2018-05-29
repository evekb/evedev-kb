# Swagger\Client\MarketApi

All URIs are relative to *https://esi.evetech.net*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdOrders**](MarketApi.md#getCharactersCharacterIdOrders) | **GET** /v2/characters/{character_id}/orders/ | List open orders from a character
[**getCharactersCharacterIdOrdersHistory**](MarketApi.md#getCharactersCharacterIdOrdersHistory) | **GET** /v1/characters/{character_id}/orders/history/ | List historical orders by a character
[**getCorporationsCorporationIdOrders**](MarketApi.md#getCorporationsCorporationIdOrders) | **GET** /v2/corporations/{corporation_id}/orders/ | List open orders from a corporation
[**getCorporationsCorporationIdOrdersHistory**](MarketApi.md#getCorporationsCorporationIdOrdersHistory) | **GET** /v1/corporations/{corporation_id}/orders/history/ | List historical orders from a corporation
[**getMarketsGroups**](MarketApi.md#getMarketsGroups) | **GET** /v1/markets/groups/ | Get item groups
[**getMarketsGroupsMarketGroupId**](MarketApi.md#getMarketsGroupsMarketGroupId) | **GET** /v1/markets/groups/{market_group_id}/ | Get item group information
[**getMarketsPrices**](MarketApi.md#getMarketsPrices) | **GET** /v1/markets/prices/ | List market prices
[**getMarketsRegionIdHistory**](MarketApi.md#getMarketsRegionIdHistory) | **GET** /v1/markets/{region_id}/history/ | List historical market statistics in a region
[**getMarketsRegionIdOrders**](MarketApi.md#getMarketsRegionIdOrders) | **GET** /v1/markets/{region_id}/orders/ | List orders in a region
[**getMarketsRegionIdTypes**](MarketApi.md#getMarketsRegionIdTypes) | **GET** /v1/markets/{region_id}/types/ | List type IDs relevant to a market
[**getMarketsStructuresStructureId**](MarketApi.md#getMarketsStructuresStructureId) | **GET** /v1/markets/structures/{structure_id}/ | List orders in a structure


# **getCharactersCharacterIdOrders**
> \Swagger\Client\Model\GetCharactersCharacterIdOrders200Ok[] getCharactersCharacterIdOrders($character_id, $datasource, $if_none_match, $token)

List open orders from a character

List open market orders placed by a character  ---  This route is cached for up to 1200 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MarketApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$token = "token_example"; // string | Access token to use if unable to set a header

try {
    $result = $api_instance->getCharactersCharacterIdOrders($character_id, $datasource, $if_none_match, $token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getCharactersCharacterIdOrders: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **token** | **string**| Access token to use if unable to set a header | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdOrders200Ok[]**](../Model/GetCharactersCharacterIdOrders200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdOrdersHistory**
> \Swagger\Client\Model\GetCharactersCharacterIdOrdersHistory200Ok[] getCharactersCharacterIdOrdersHistory($character_id, $datasource, $if_none_match, $page, $token)

List historical orders by a character

List cancelled and expired market orders placed by a character up to 90 days in the past.  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MarketApi();
$character_id = 56; // int | An EVE character ID
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$page = 1; // int | Which page of results to return
$token = "token_example"; // string | Access token to use if unable to set a header

try {
    $result = $api_instance->getCharactersCharacterIdOrdersHistory($character_id, $datasource, $if_none_match, $page, $token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getCharactersCharacterIdOrdersHistory: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| An EVE character ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **page** | **int**| Which page of results to return | [optional] [default to 1]
 **token** | **string**| Access token to use if unable to set a header | [optional]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdOrdersHistory200Ok[]**](../Model/GetCharactersCharacterIdOrdersHistory200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdOrders**
> \Swagger\Client\Model\GetCorporationsCorporationIdOrders200Ok[] getCorporationsCorporationIdOrders($corporation_id, $datasource, $if_none_match, $page, $token)

List open orders from a corporation

List open market orders placed on behalf of a corporation  ---  This route is cached for up to 1200 seconds  --- Requires one of the following EVE corporation role(s): Accountant, Trader  --- Warning: This route has an upgrade available.  --- [Diff of the upcoming changes](https://esi.evetech.net/diff/latest/dev/#GET-/corporations/{corporation_id}/orders/)

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MarketApi();
$corporation_id = 56; // int | An EVE corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$page = 1; // int | Which page of results to return
$token = "token_example"; // string | Access token to use if unable to set a header

try {
    $result = $api_instance->getCorporationsCorporationIdOrders($corporation_id, $datasource, $if_none_match, $page, $token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getCorporationsCorporationIdOrders: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **page** | **int**| Which page of results to return | [optional] [default to 1]
 **token** | **string**| Access token to use if unable to set a header | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdOrders200Ok[]**](../Model/GetCorporationsCorporationIdOrders200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCorporationsCorporationIdOrdersHistory**
> \Swagger\Client\Model\GetCorporationsCorporationIdOrdersHistory200Ok[] getCorporationsCorporationIdOrdersHistory($corporation_id, $datasource, $if_none_match, $page, $token)

List historical orders from a corporation

List cancelled and expired market orders placed on behalf of a corporation up to 90 days in the past.  ---  This route is cached for up to 3600 seconds  --- Requires one of the following EVE corporation role(s): Accountant, Trader  --- Warning: This route has an upgrade available.  --- [Diff of the upcoming changes](https://esi.evetech.net/diff/latest/dev/#GET-/corporations/{corporation_id}/orders/history/)

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MarketApi();
$corporation_id = 56; // int | An EVE corporation ID
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$page = 1; // int | Which page of results to return
$token = "token_example"; // string | Access token to use if unable to set a header

try {
    $result = $api_instance->getCorporationsCorporationIdOrdersHistory($corporation_id, $datasource, $if_none_match, $page, $token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getCorporationsCorporationIdOrdersHistory: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **corporation_id** | **int**| An EVE corporation ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **page** | **int**| Which page of results to return | [optional] [default to 1]
 **token** | **string**| Access token to use if unable to set a header | [optional]

### Return type

[**\Swagger\Client\Model\GetCorporationsCorporationIdOrdersHistory200Ok[]**](../Model/GetCorporationsCorporationIdOrdersHistory200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsGroups**
> int[] getMarketsGroups($datasource, $if_none_match)

Get item groups

Get a list of item groups  ---  This route expires daily at 11:05

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\MarketApi();
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag

try {
    $result = $api_instance->getMarketsGroups($datasource, $if_none_match);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsGroups: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsGroupsMarketGroupId**
> \Swagger\Client\Model\GetMarketsGroupsMarketGroupIdOk getMarketsGroupsMarketGroupId($market_group_id, $accept_language, $datasource, $if_none_match, $language)

Get item group information

Get information on an item group  ---  This route expires daily at 11:05

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\MarketApi();
$market_group_id = 56; // int | An Eve item group ID
$accept_language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$language = "en-us"; // string | Language to use in the response, takes precedence over Accept-Language

try {
    $result = $api_instance->getMarketsGroupsMarketGroupId($market_group_id, $accept_language, $datasource, $if_none_match, $language);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsGroupsMarketGroupId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **market_group_id** | **int**| An Eve item group ID |
 **accept_language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **language** | **string**| Language to use in the response, takes precedence over Accept-Language | [optional] [default to en-us]

### Return type

[**\Swagger\Client\Model\GetMarketsGroupsMarketGroupIdOk**](../Model/GetMarketsGroupsMarketGroupIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsPrices**
> \Swagger\Client\Model\GetMarketsPrices200Ok[] getMarketsPrices($datasource, $if_none_match)

List market prices

Return a list of prices  ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\MarketApi();
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag

try {
    $result = $api_instance->getMarketsPrices($datasource, $if_none_match);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsPrices: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]

### Return type

[**\Swagger\Client\Model\GetMarketsPrices200Ok[]**](../Model/GetMarketsPrices200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsRegionIdHistory**
> \Swagger\Client\Model\GetMarketsRegionIdHistory200Ok[] getMarketsRegionIdHistory($region_id, $type_id, $datasource, $if_none_match)

List historical market statistics in a region

Return a list of historical market statistics for the specified type in a region  ---  This route expires daily at 11:05

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\MarketApi();
$region_id = 56; // int | Return statistics in this region
$type_id = 56; // int | Return statistics for this type
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag

try {
    $result = $api_instance->getMarketsRegionIdHistory($region_id, $type_id, $datasource, $if_none_match);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsRegionIdHistory: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **region_id** | **int**| Return statistics in this region |
 **type_id** | **int**| Return statistics for this type |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]

### Return type

[**\Swagger\Client\Model\GetMarketsRegionIdHistory200Ok[]**](../Model/GetMarketsRegionIdHistory200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsRegionIdOrders**
> \Swagger\Client\Model\GetMarketsRegionIdOrders200Ok[] getMarketsRegionIdOrders($order_type, $region_id, $datasource, $if_none_match, $page, $type_id)

List orders in a region

Return a list of orders in a region  ---  This route is cached for up to 300 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\MarketApi();
$order_type = "all"; // string | Filter buy/sell orders, return all orders by default. If you query without type_id, we always return both buy and sell orders.
$region_id = 56; // int | Return orders in this region
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$page = 1; // int | Which page of results to return
$type_id = 56; // int | Return orders only for this type

try {
    $result = $api_instance->getMarketsRegionIdOrders($order_type, $region_id, $datasource, $if_none_match, $page, $type_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsRegionIdOrders: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **order_type** | **string**| Filter buy/sell orders, return all orders by default. If you query without type_id, we always return both buy and sell orders. | [default to all]
 **region_id** | **int**| Return orders in this region |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **page** | **int**| Which page of results to return | [optional] [default to 1]
 **type_id** | **int**| Return orders only for this type | [optional]

### Return type

[**\Swagger\Client\Model\GetMarketsRegionIdOrders200Ok[]**](../Model/GetMarketsRegionIdOrders200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsRegionIdTypes**
> int[] getMarketsRegionIdTypes($region_id, $datasource, $if_none_match, $page)

List type IDs relevant to a market

Return a list of type IDs that have active orders in the region, for efficient market indexing.  ---  This route is cached for up to 600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\MarketApi();
$region_id = 56; // int | Return statistics in this region
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$page = 1; // int | Which page of results to return

try {
    $result = $api_instance->getMarketsRegionIdTypes($region_id, $datasource, $if_none_match, $page);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsRegionIdTypes: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **region_id** | **int**| Return statistics in this region |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **page** | **int**| Which page of results to return | [optional] [default to 1]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getMarketsStructuresStructureId**
> \Swagger\Client\Model\GetMarketsStructuresStructureId200Ok[] getMarketsStructuresStructureId($structure_id, $datasource, $if_none_match, $page, $token)

List orders in a structure

Return all orders in a structure  ---  This route is cached for up to 300 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\MarketApi();
$structure_id = 789; // int | Return orders in this structure
$datasource = "tranquility"; // string | The server name you would like data from
$if_none_match = "if_none_match_example"; // string | ETag from a previous request. A 304 will be returned if this matches the current ETag
$page = 1; // int | Which page of results to return
$token = "token_example"; // string | Access token to use if unable to set a header

try {
    $result = $api_instance->getMarketsStructuresStructureId($structure_id, $datasource, $if_none_match, $page, $token);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling MarketApi->getMarketsStructuresStructureId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **structure_id** | **int**| Return orders in this structure |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]
 **if_none_match** | **string**| ETag from a previous request. A 304 will be returned if this matches the current ETag | [optional]
 **page** | **int**| Which page of results to return | [optional] [default to 1]
 **token** | **string**| Access token to use if unable to set a header | [optional]

### Return type

[**\Swagger\Client\Model\GetMarketsStructuresStructureId200Ok[]**](../Model/GetMarketsStructuresStructureId200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

