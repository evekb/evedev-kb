# Swagger\Client\CalendarApi

All URIs are relative to *https://esi.tech.ccp.is/latest*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getCharactersCharacterIdCalendar**](CalendarApi.md#getCharactersCharacterIdCalendar) | **GET** /characters/{character_id}/calendar/ | List calendar event summaries
[**getCharactersCharacterIdCalendarEventId**](CalendarApi.md#getCharactersCharacterIdCalendarEventId) | **GET** /characters/{character_id}/calendar/{event_id}/ | Get an event
[**putCharactersCharacterIdCalendarEventId**](CalendarApi.md#putCharactersCharacterIdCalendarEventId) | **PUT** /characters/{character_id}/calendar/{event_id}/ | Respond to an event


# **getCharactersCharacterIdCalendar**
> \Swagger\Client\Model\GetCharactersCharacterIdCalendar200Ok[] getCharactersCharacterIdCalendar($character_id, $from_event, $datasource)

List calendar event summaries

Get 50 event summaries from the calendar. If no event ID is given, the resource will return the next 50 chronological event summaries from now. If an event ID is specified, it will return the next 50 chronological event summaries from after that event.   ---  Alternate route: `/v1/characters/{character_id}/calendar/`  Alternate route: `/legacy/characters/{character_id}/calendar/`  Alternate route: `/dev/characters/{character_id}/calendar/`   ---  This route is cached for up to 5 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CalendarApi();
$character_id = 56; // int | The character to retrieve events from
$from_event = 56; // int | The event ID to retrieve events from
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdCalendar($character_id, $from_event, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CalendarApi->getCharactersCharacterIdCalendar: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| The character to retrieve events from |
 **from_event** | **int**| The event ID to retrieve events from | [optional]
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdCalendar200Ok[]**](../Model/GetCharactersCharacterIdCalendar200Ok.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **getCharactersCharacterIdCalendarEventId**
> \Swagger\Client\Model\GetCharactersCharacterIdCalendarEventIdOk getCharactersCharacterIdCalendarEventId($character_id, $event_id, $datasource)

Get an event

Get all the information for a specific event  ---  Alternate route: `/v3/characters/{character_id}/calendar/{event_id}/`  Alternate route: `/dev/characters/{character_id}/calendar/{event_id}/`   ---  This route is cached for up to 5 seconds

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CalendarApi();
$character_id = 56; // int | The character id requesting the event
$event_id = 56; // int | The id of the event requested
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $result = $api_instance->getCharactersCharacterIdCalendarEventId($character_id, $event_id, $datasource);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling CalendarApi->getCharactersCharacterIdCalendarEventId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| The character id requesting the event |
 **event_id** | **int**| The id of the event requested |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

[**\Swagger\Client\Model\GetCharactersCharacterIdCalendarEventIdOk**](../Model/GetCharactersCharacterIdCalendarEventIdOk.md)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

# **putCharactersCharacterIdCalendarEventId**
> putCharactersCharacterIdCalendarEventId($character_id, $event_id, $response, $datasource)

Respond to an event

Set your response status to an event  ---  Alternate route: `/v3/characters/{character_id}/calendar/{event_id}/`  Alternate route: `/dev/characters/{character_id}/calendar/{event_id}/`

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure OAuth2 access token for authorization: evesso
Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');

$api_instance = new Swagger\Client\Api\CalendarApi();
$character_id = 56; // int | The character ID requesting the event
$event_id = 56; // int | The ID of the event requested
$response = new \Swagger\Client\Model\PutCharactersCharacterIdCalendarEventIdResponse(); // \Swagger\Client\Model\PutCharactersCharacterIdCalendarEventIdResponse | The response value to set, overriding current value.
$datasource = "tranquility"; // string | The server name you would like data from

try {
    $api_instance->putCharactersCharacterIdCalendarEventId($character_id, $event_id, $response, $datasource);
} catch (Exception $e) {
    echo 'Exception when calling CalendarApi->putCharactersCharacterIdCalendarEventId: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **character_id** | **int**| The character ID requesting the event |
 **event_id** | **int**| The ID of the event requested |
 **response** | [**\Swagger\Client\Model\PutCharactersCharacterIdCalendarEventIdResponse**](../Model/\Swagger\Client\Model\PutCharactersCharacterIdCalendarEventIdResponse.md)| The response value to set, overriding current value. |
 **datasource** | **string**| The server name you would like data from | [optional] [default to tranquility]

### Return type

void (empty response body)

### Authorization

[evesso](../../README.md#evesso)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

