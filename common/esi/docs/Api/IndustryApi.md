# Swagger\Client\IndustryApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getIndustryFacilities**](IndustryApi.md#getIndustryFacilities) | **GET** /industry/facilities/ | List industry facilities
[**getIndustrySystems**](IndustryApi.md#getIndustrySystems) | **GET** /industry/systems/ | List solar system cost indices


# **getIndustryFacilities**
> \Swagger\Client\Model\GetIndustryFacilities200Ok[] getIndustryFacilities($datasource)

List industry facilities

Return a list of industry facilities  ---  Alternate route: `/v1/industry/facilities/`  Alternate route: `/legacy/industry/facilities/`  Alternate route: `/dev/industry/facilities/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\IndustryApi();
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getIndustryFacilities($datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IndustryApi->getIndustryFacilities: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetIndustryFacilities200Ok[]**](../Model/GetIndustryFacilities200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getIndustrySystems**
> \Swagger\Client\Model\GetIndustrySystems200Ok[] getIndustrySystems($datasource)

List solar system cost indices

Return cost indices for solar systems  ---  Alternate route: `/v1/industry/systems/`  Alternate route: `/legacy/industry/systems/`  Alternate route: `/dev/industry/systems/`   ---  This route is cached for up to 3600 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\IndustryApi();
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getIndustrySystems($datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling IndustryApi->getIndustrySystems: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetIndustrySystems200Ok[]**](../Model/GetIndustrySystems200Ok.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

