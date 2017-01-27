# Swagger\Client\FleetsApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**deleteFleetsFleetIdMembersMemberId**](FleetsApi.md#deleteFleetsFleetIdMembersMemberId) | **DELETE** /fleets/{fleet_id}/members/{member_id}/ | Kick fleet member
[**deleteFleetsFleetIdSquadsSquadId**](FleetsApi.md#deleteFleetsFleetIdSquadsSquadId) | **DELETE** /fleets/{fleet_id}/squads/{squad_id}/ | Delete fleet squad
[**deleteFleetsFleetIdWingsWingId**](FleetsApi.md#deleteFleetsFleetIdWingsWingId) | **DELETE** /fleets/{fleet_id}/wings/{wing_id}/ | Delete fleet wing
[**getFleetsFleetId**](FleetsApi.md#getFleetsFleetId) | **GET** /fleets/{fleet_id}/ | Get fleet information
[**getFleetsFleetIdMembers**](FleetsApi.md#getFleetsFleetIdMembers) | **GET** /fleets/{fleet_id}/members/ | Get fleet members
[**getFleetsFleetIdWings**](FleetsApi.md#getFleetsFleetIdWings) | **GET** /fleets/{fleet_id}/wings/ | Get fleet wings
[**postFleetsFleetIdMembers**](FleetsApi.md#postFleetsFleetIdMembers) | **POST** /fleets/{fleet_id}/members/ | Create fleet invitation
[**postFleetsFleetIdWings**](FleetsApi.md#postFleetsFleetIdWings) | **POST** /fleets/{fleet_id}/wings/ | Create fleet wing
[**postFleetsFleetIdWingsWingIdSquads**](FleetsApi.md#postFleetsFleetIdWingsWingIdSquads) | **POST** /fleets/{fleet_id}/wings/{wing_id}/squads/ | Create fleet squad
[**putFleetsFleetId**](FleetsApi.md#putFleetsFleetId) | **PUT** /fleets/{fleet_id}/ | Update fleet
[**putFleetsFleetIdMembersMemberId**](FleetsApi.md#putFleetsFleetIdMembersMemberId) | **PUT** /fleets/{fleet_id}/members/{member_id}/ | Move fleet member
[**putFleetsFleetIdSquadsSquadId**](FleetsApi.md#putFleetsFleetIdSquadsSquadId) | **PUT** /fleets/{fleet_id}/squads/{squad_id}/ | Rename fleet squad
[**putFleetsFleetIdWingsWingId**](FleetsApi.md#putFleetsFleetIdWingsWingId) | **PUT** /fleets/{fleet_id}/wings/{wing_id}/ | Rename fleet wing


# **deleteFleetsFleetIdMembersMemberId**
> deleteFleetsFleetIdMembersMemberId($fleet_id, $member_id, $datasource)

Kick fleet member

Kick a fleet member  ---  Alternate route: `/v1/fleets/{fleet_id}/members/{member_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/members/{member_id}/`  Alternate route: `/dev/fleets/{fleet_id}/members/{member_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$member_id = 56; // int | The character ID of a member in this fleet
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteFleetsFleetIdMembersMemberId($fleet_id, $member_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->deleteFleetsFleetIdMembersMemberId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **member_id** | **int**| The character ID of a member in this fleet |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteFleetsFleetIdSquadsSquadId**
> deleteFleetsFleetIdSquadsSquadId($fleet_id, $squad_id, $datasource)

Delete fleet squad

Delete a fleet squad, only empty squads can be deleted  ---  Alternate route: `/v1/fleets/{fleet_id}/squads/{squad_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/squads/{squad_id}/`  Alternate route: `/dev/fleets/{fleet_id}/squads/{squad_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$squad_id = 789; // int | The squad to delete
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteFleetsFleetIdSquadsSquadId($fleet_id, $squad_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->deleteFleetsFleetIdSquadsSquadId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **squad_id** | **int**| The squad to delete |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **deleteFleetsFleetIdWingsWingId**
> deleteFleetsFleetIdWingsWingId($fleet_id, $wing_id, $datasource)

Delete fleet wing

Delete a fleet wing, only empty wings can be deleted. The wing may contain squads, but the squads must be empty  ---  Alternate route: `/v1/fleets/{fleet_id}/wings/{wing_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/wings/{wing_id}/`  Alternate route: `/dev/fleets/{fleet_id}/wings/{wing_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$wing_id = 789; // int | The wing to delete
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->deleteFleetsFleetIdWingsWingId($fleet_id, $wing_id, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->deleteFleetsFleetIdWingsWingId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **wing_id** | **int**| The wing to delete |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getFleetsFleetId**
> \Swagger\Client\Model\GetFleetsFleetIdOk getFleetsFleetId($fleet_id, $datasource)

Get fleet information

Return details about a fleet  ---  Alternate route: `/v1/fleets/{fleet_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/`  Alternate route: `/dev/fleets/{fleet_id}/`   ---  This route is cached for up to 5 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getFleetsFleetId($fleet_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->getFleetsFleetId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetFleetsFleetIdOk**](../Model/GetFleetsFleetIdOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getFleetsFleetIdMembers**
> \Swagger\Client\Model\GetFleetsFleetIdMembers200Ok[] getFleetsFleetIdMembers($fleet_id, $language, $datasource)

Get fleet members

Return information about fleet members  ---  Alternate route: `/v1/fleets/{fleet_id}/members/`  Alternate route: `/legacy/fleets/{fleet_id}/members/`  Alternate route: `/dev/fleets/{fleet_id}/members/`   ---  This route is cached for up to 5 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getFleetsFleetIdMembers($fleet_id, $language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->getFleetsFleetIdMembers: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetFleetsFleetIdMembers200Ok[]**](../Model/GetFleetsFleetIdMembers200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getFleetsFleetIdWings**
> \Swagger\Client\Model\GetFleetsFleetIdWings200Ok[] getFleetsFleetIdWings($fleet_id, $language, $datasource)

Get fleet wings

Return information about wings in a fleet  ---  Alternate route: `/v1/fleets/{fleet_id}/wings/`  Alternate route: `/legacy/fleets/{fleet_id}/wings/`  Alternate route: `/dev/fleets/{fleet_id}/wings/`   ---  This route is cached for up to 5 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$language = "en-us"; // string | Language to use in the response
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getFleetsFleetIdWings($fleet_id, $language, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->getFleetsFleetIdWings: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **language** | **string**| Language to use in the response | [optional] [default to en-us]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetFleetsFleetIdWings200Ok[]**](../Model/GetFleetsFleetIdWings200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postFleetsFleetIdMembers**
> postFleetsFleetIdMembers($fleet_id, $invitation, $datasource)

Create fleet invitation

Invite a character into the fleet, if a character has a CSPA charge set, it is not possible to invite them to the fleet using ESI  ---  Alternate route: `/v1/fleets/{fleet_id}/members/`  Alternate route: `/legacy/fleets/{fleet_id}/members/`  Alternate route: `/dev/fleets/{fleet_id}/members/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$invitation = new \Swagger\Client\Model\PostFleetsFleetIdMembersInvitation(); // \Swagger\Client\Model\PostFleetsFleetIdMembersInvitation | Details of the invitation
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->postFleetsFleetIdMembers($fleet_id, $invitation, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->postFleetsFleetIdMembers: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **invitation** | [**\Swagger\Client\Model\PostFleetsFleetIdMembersInvitation**](../Model/\Swagger\Client\Model\PostFleetsFleetIdMembersInvitation.md)| Details of the invitation |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postFleetsFleetIdWings**
> \Swagger\Client\Model\PostFleetsFleetIdWingsCreated postFleetsFleetIdWings($fleet_id, $datasource)

Create fleet wing

Create a new wing in a fleet  ---  Alternate route: `/v1/fleets/{fleet_id}/wings/`  Alternate route: `/legacy/fleets/{fleet_id}/wings/`  Alternate route: `/dev/fleets/{fleet_id}/wings/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postFleetsFleetIdWings($fleet_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->postFleetsFleetIdWings: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\PostFleetsFleetIdWingsCreated**](../Model/PostFleetsFleetIdWingsCreated.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **postFleetsFleetIdWingsWingIdSquads**
> \Swagger\Client\Model\PostFleetsFleetIdWingsWingIdSquadsCreated postFleetsFleetIdWingsWingIdSquads($fleet_id, $wing_id, $datasource)

Create fleet squad

Create a new squad in a fleet  ---  Alternate route: `/v1/fleets/{fleet_id}/wings/{wing_id}/squads/`  Alternate route: `/legacy/fleets/{fleet_id}/wings/{wing_id}/squads/`  Alternate route: `/dev/fleets/{fleet_id}/wings/{wing_id}/squads/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$wing_id = 789; // int | The wing_id to create squad in
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->postFleetsFleetIdWingsWingIdSquads($fleet_id, $wing_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->postFleetsFleetIdWingsWingIdSquads: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **wing_id** | **int**| The wing_id to create squad in |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\PostFleetsFleetIdWingsWingIdSquadsCreated**](../Model/PostFleetsFleetIdWingsWingIdSquadsCreated.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putFleetsFleetId**
> putFleetsFleetId($fleet_id, $new_settings, $datasource)

Update fleet

Update settings about a fleet  ---  Alternate route: `/v1/fleets/{fleet_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/`  Alternate route: `/dev/fleets/{fleet_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$new_settings = new \Swagger\Client\Model\PutFleetsFleetIdNewSettings(); // \Swagger\Client\Model\PutFleetsFleetIdNewSettings | What to update for this fleet
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putFleetsFleetId($fleet_id, $new_settings, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->putFleetsFleetId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **new_settings** | [**\Swagger\Client\Model\PutFleetsFleetIdNewSettings**](../Model/\Swagger\Client\Model\PutFleetsFleetIdNewSettings.md)| What to update for this fleet |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putFleetsFleetIdMembersMemberId**
> putFleetsFleetIdMembersMemberId($fleet_id, $member_id, $movement, $datasource)

Move fleet member

Move a fleet member around  ---  Alternate route: `/v1/fleets/{fleet_id}/members/{member_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/members/{member_id}/`  Alternate route: `/dev/fleets/{fleet_id}/members/{member_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$member_id = 56; // int | The character ID of a member in this fleet
$movement = new \Swagger\Client\Model\PutFleetsFleetIdMembersMemberIdMovement(); // \Swagger\Client\Model\PutFleetsFleetIdMembersMemberIdMovement | Details of the invitation
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putFleetsFleetIdMembersMemberId($fleet_id, $member_id, $movement, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->putFleetsFleetIdMembersMemberId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **member_id** | **int**| The character ID of a member in this fleet |
 **movement** | [**\Swagger\Client\Model\PutFleetsFleetIdMembersMemberIdMovement**](../Model/\Swagger\Client\Model\PutFleetsFleetIdMembersMemberIdMovement.md)| Details of the invitation |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putFleetsFleetIdSquadsSquadId**
> putFleetsFleetIdSquadsSquadId($fleet_id, $squad_id, $naming, $datasource)

Rename fleet squad

Rename a fleet squad  ---  Alternate route: `/v1/fleets/{fleet_id}/squads/{squad_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/squads/{squad_id}/`  Alternate route: `/dev/fleets/{fleet_id}/squads/{squad_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$squad_id = 789; // int | The squad to rename
$naming = new \Swagger\Client\Model\PutFleetsFleetIdSquadsSquadIdNaming(); // \Swagger\Client\Model\PutFleetsFleetIdSquadsSquadIdNaming | New name of the squad
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putFleetsFleetIdSquadsSquadId($fleet_id, $squad_id, $naming, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->putFleetsFleetIdSquadsSquadId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **squad_id** | **int**| The squad to rename |
 **naming** | [**\Swagger\Client\Model\PutFleetsFleetIdSquadsSquadIdNaming**](../Model/\Swagger\Client\Model\PutFleetsFleetIdSquadsSquadIdNaming.md)| New name of the squad |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putFleetsFleetIdWingsWingId**
> putFleetsFleetIdWingsWingId($fleet_id, $wing_id, $naming, $datasource)

Rename fleet wing

Rename a fleet wing  ---  Alternate route: `/v1/fleets/{fleet_id}/wings/{wing_id}/`  Alternate route: `/legacy/fleets/{fleet_id}/wings/{wing_id}/`  Alternate route: `/dev/fleets/{fleet_id}/wings/{wing_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\FleetsApi();
$fleet_id = 789; // int | ID for a fleet
$wing_id = 789; // int | The wing to rename
$naming = new \Swagger\Client\Model\PutFleetsFleetIdWingsWingIdNaming(); // \Swagger\Client\Model\PutFleetsFleetIdWingsWingIdNaming | New name of the wing
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putFleetsFleetIdWingsWingId($fleet_id, $wing_id, $naming, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling FleetsApi->putFleetsFleetIdWingsWingId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **fleet_id** | **int**| ID for a fleet |
 **wing_id** | **int**| The wing to rename |
 **naming** | [**\Swagger\Client\Model\PutFleetsFleetIdWingsWingIdNaming**](../Model/\Swagger\Client\Model\PutFleetsFleetIdWingsWingIdNaming.md)| New name of the wing |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

