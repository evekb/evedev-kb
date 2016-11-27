# GetSovereigntyCampaigns200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**attackers_score** | **float** | Score for all attacking parties, only present in Defense Events. | [optional] 
**campaign_id** | **int** | Unique ID for this campaign. | 
**constellation_id** | **int** | The constellation in which the campaign will take place. | 
**defender_id** | **int** | Defending alliance, only present in Defense Events | [optional] 
**defender_score** | **float** | Score for the defending alliance, only present in Defense Events. | [optional] 
**event_type** | **string** | Type of event this campaign is for. tcu_defense, ihub_defense and station_defense are referred to as \&quot;Defense Events\&quot;, station_freeport as \&quot;Freeport Events\&quot;. | 
**participants** | [**\Swagger\Client\Model\SovereigntycampaignsParticipants[]**](SovereigntycampaignsParticipants.md) | Alliance participating and their respective scores, only present in Freeport Events. | [optional] 
**solar_system_id** | **int** | The solar system the structure is located in. | 
**start_time** | [**\DateTime**](\DateTime.md) | Time the event is scheduled to start. | 
**structure_id** | **int** | The structure item ID that is related to this campaign. | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


