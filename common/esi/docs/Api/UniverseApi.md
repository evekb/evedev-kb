# Swagger\Client\UniverseApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getUniverseCategories**](UniverseApi.md#getUniverseCategories) | **GET** /universe/categories/ | Get item categories
[**getUniverseCategoriesCategoryId**](UniverseApi.md#getUniverseCategoriesCategoryId) | **GET** /universe/categories/{category_id}/ | Get item category information
[**getUniverseFactions**](UniverseApi.md#getUniverseFactions) | **GET** /universe/factions/ | Get factions
[**getUniverseGroups**](UniverseApi.md#getUniverseGroups) | **GET** /universe/groups/ | Get item groups
[**getUniverseGroupsGroupId**](UniverseApi.md#getUniverseGroupsGroupId) | **GET** /universe/groups/{group_id}/ | Get item group information
[**getUniverseRaces**](UniverseApi.md#getUniverseRaces) | **GET** /universe/races/ | Get character races
[**getUniverseStationsStationId**](UniverseApi.md#getUniverseStationsStationId) | **GET** /universe/stations/{station_id}/ | Get station information
[**getUniverseStructures**](UniverseApi.md#getUniverseStructures) | **GET** /universe/structures/ | List all public structures
[**getUniverseStructuresStructureId**](UniverseApi.md#getUniverseStructuresStructureId) | **GET** /universe/structures/{structure_id}/ | Get structure information
[**getUniverseSystemsSystemId**](UniverseApi.md#getUniverseSystemsSystemId) | **GET** /universe/systems/{system_id}/ | Get solar system information
[**getUniverseTypes**](UniverseApi.md#getUniverseTypes) | **GET** /universe/types/ | Get types
[**getUniverseTypesTypeId**](UniverseApi.md#getUniverseTypesTypeId) | **GET** /universe/types/{type_id}/ | Get type information
[**postUniverseNames**](UniverseApi.md#postUniverseNames) | **POST** /universe/names/ | Get names and categories for a set of ID&#39;s


# **getUniverseCategories**
> int[] getUniverseCategories($datasource)

Get item categories

Get a list of item categories  ---  Alternate route: `/v1/universe/categories/`  Alternate route: `/legacy/universe/categories/`  Alternate route: `/dev/universe/categories/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseCategories($datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseCategories: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseCategoriesCategoryId**
> \Swagger\Client\Model\GetUniverseCategoriesCategoryIdOk getUniverseCategoriesCategoryId($category_id, $language, $datasource)

Get item category information

Get information of an item category  ---  Alternate route: `/v1/universe/categories/{category_id}/`  Alternate route: `/legacy/universe/categories/{category_id}/`  Alternate route: `/dev/universe/categories/{category_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$category_id = 56; // int | An Eve item category ID
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseCategoriesCategoryId($category_id, $language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseCategoriesCategoryId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **category_id** | **int**| An Eve item category ID |
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseCategoriesCategoryIdOk**](../Model/GetUniverseCategoriesCategoryIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseFactions**
> \Swagger\Client\Model\GetUniverseFactions200Ok[] getUniverseFactions($language, $datasource)

Get factions

Get a list of factions  ---  Alternate route: `/v1/universe/factions/`  Alternate route: `/legacy/universe/factions/`  Alternate route: `/dev/universe/factions/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseFactions($language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseFactions: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseFactions200Ok[]**](../Model/GetUniverseFactions200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseGroups**
> int[] getUniverseGroups($page, $datasource)

Get item groups

Get a list of item groups  ---  Alternate route: `/v1/universe/groups/`  Alternate route: `/legacy/universe/groups/`  Alternate route: `/dev/universe/groups/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$page = 56; // int | Which page to query
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseGroups($page, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseGroups: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **page** | **int**| Which page to query | [optional]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseGroupsGroupId**
> \Swagger\Client\Model\GetUniverseGroupsGroupIdOk getUniverseGroupsGroupId($group_id, $language, $datasource)

Get item group information

Get information on an item group  ---  Alternate route: `/v1/universe/groups/{group_id}/`  Alternate route: `/legacy/universe/groups/{group_id}/`  Alternate route: `/dev/universe/groups/{group_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$group_id = 56; // int | An Eve item group ID
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseGroupsGroupId($group_id, $language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseGroupsGroupId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **group_id** | **int**| An Eve item group ID |
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseGroupsGroupIdOk**](../Model/GetUniverseGroupsGroupIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseRaces**
> \Swagger\Client\Model\GetUniverseRaces200Ok[] getUniverseRaces($language, $datasource)

Get character races

Get a list of character races  ---  Alternate route: `/v1/universe/races/`  Alternate route: `/legacy/universe/races/`  Alternate route: `/dev/universe/races/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseRaces($language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseRaces: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseRaces200Ok[]**](../Model/GetUniverseRaces200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseStationsStationId**
> \Swagger\Client\Model\GetUniverseStationsStationIdOk getUniverseStationsStationId($station_id, $datasource)

Get station information

Public information on stations  ---  Alternate route: `/v1/universe/stations/{station_id}/`  Alternate route: `/legacy/universe/stations/{station_id}/`  Alternate route: `/dev/universe/stations/{station_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$station_id = 56; // int | An Eve station ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseStationsStationId($station_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseStationsStationId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **station_id** | **int**| An Eve station ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseStationsStationIdOk**](../Model/GetUniverseStationsStationIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseStructures**
> int[] getUniverseStructures($datasource)

List all public structures

List all public structures  ---  Alternate route: `/v1/universe/structures/`  Alternate route: `/legacy/universe/structures/`  Alternate route: `/dev/universe/structures/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseStructures($datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseStructures: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseStructuresStructureId**
> \Swagger\Client\Model\GetUniverseStructuresStructureIdOk getUniverseStructuresStructureId($structure_id, $datasource)

Get structure information

Returns information on requested structure, if you are on the ACL. Otherwise, returns \"Forbidden\" for all inputs.  ---  Alternate route: `/v1/universe/structures/{structure_id}/`  Alternate route: `/legacy/universe/structures/{structure_id}/`  Alternate route: `/dev/universe/structures/{structure_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\UniverseApi();
$structure_id = 789; // int | An Eve structure ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseStructuresStructureId($structure_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseStructuresStructureId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **structure_id** | **int**| An Eve structure ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseStructuresStructureIdOk**](../Model/GetUniverseStructuresStructureIdOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseSystemsSystemId**
> \Swagger\Client\Model\GetUniverseSystemsSystemIdOk getUniverseSystemsSystemId($system_id, $datasource)

Get solar system information

Information on solar systems  ---  Alternate route: `/v1/universe/systems/{system_id}/`  Alternate route: `/legacy/universe/systems/{system_id}/`  Alternate route: `/dev/universe/systems/{system_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$system_id = 56; // int | An Eve solar system ID
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseSystemsSystemId($system_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseSystemsSystemId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **system_id** | **int**| An Eve solar system ID |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseSystemsSystemIdOk**](../Model/GetUniverseSystemsSystemIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseTypes**
> int[] getUniverseTypes($page, $datasource)

Get types

Get a list of type ids  ---  Alternate route: `/v1/universe/types/`  Alternate route: `/legacy/universe/types/`  Alternate route: `/dev/universe/types/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$page = 56; // int | Which page to query
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseTypes($page, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseTypes: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **page** | **int**| Which page to query | [optional]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

**int[]**

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getUniverseTypesTypeId**
> \Swagger\Client\Model\GetUniverseTypesTypeIdOk getUniverseTypesTypeId($type_id, $language, $datasource)

Get type information

Get information on a type  ---  Alternate route: `/v2/universe/types/{type_id}/`  Alternate route: `/dev/universe/types/{type_id}/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$type_id = 56; // int | An Eve item type ID
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getUniverseTypesTypeId($type_id, $language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->getUniverseTypesTypeId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **type_id** | **int**| An Eve item type ID |
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetUniverseTypesTypeIdOk**](../Model/GetUniverseTypesTypeIdOk.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postUniverseNames**
> \Swagger\Client\Model\PostUniverseNames200Ok[] postUniverseNames($ids, $datasource)

Get names and categories for a set of ID's

Resolve a set of IDs to names and categories. Supported ID's for resolving are: Characters, Corporations, Alliances, Stations, Solar Systems, Constellations, Regions, Types.  ---  Alternate route: `/v1/universe/names/`  Alternate route: `/legacy/universe/names/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\UniverseApi();
$ids = new \Swagger\Client\Model\PostUniverseNamesIds(); // \Swagger\Client\Model\PostUniverseNamesIds | The ids to resolve
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postUniverseNames($ids, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UniverseApi->postUniverseNames: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **ids** | [**\Swagger\Client\Model\PostUniverseNamesIds**](../Model/\Swagger\Client\Model\PostUniverseNamesIds.md)| The ids to resolve |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\PostUniverseNames200Ok[]**](../Model/PostUniverseNames200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

