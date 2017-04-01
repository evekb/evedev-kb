# GetCorporationsCorporationIdStructures200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**corporation_id** | **int** | ID of the corporation that owns the structure | 
**current_vul** | [**\Swagger\Client\Model\CorporationscorporationIdstructuresCurrentVul[]**](CorporationscorporationIdstructuresCurrentVul.md) | This week&#39;s vulnerability windows, Monday is day 0 | 
**fuel_expires** | [**\DateTime**](Date.md) | Date on which the structure will run out of fuel | [optional] 
**next_vul** | [**\Swagger\Client\Model\CorporationscorporationIdstructuresNextVul[]**](CorporationscorporationIdstructuresNextVul.md) | Next week&#39;s vulnerability windows, Monday is day 0 | 
**profile_id** | **int** | The id of the ACL profile for this citadel | 
**services** | [**\Swagger\Client\Model\CorporationscorporationIdstructuresServices[]**](CorporationscorporationIdstructuresServices.md) | Contains a list of service upgrades, and their state | [optional] 
**state_timer_end** | [**\DateTime**](Date.md) | Date at which the structure will move to it&#39;s next state | [optional] 
**state_timer_start** | [**\DateTime**](Date.md) | Date at which the structure entered it&#39;s current state | [optional] 
**structure_id** | **int** | The Item ID of the structure | 
**system_id** | **int** | The solar system the structure is in | 
**type_id** | **int** | The type id of the structure | 
**unanchors_at** | [**\DateTime**](Date.md) | Date at which the structure will unanchor | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


