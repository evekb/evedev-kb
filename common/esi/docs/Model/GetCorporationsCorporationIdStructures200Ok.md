# GetCorporationsCorporationIdStructures200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**structure_id** | **int** | The Item ID of the structure | 
**type_id** | **int** | The type id of the structure | 
**corporation_id** | **int** | ID of the corporation that owns the structure | 
**system_id** | **int** | The solar system the structure is in | 
**profile_id** | **int** | The id of the ACL profile for this citadel | 
**fuel_expires** | [**\DateTime**](\DateTime.md) | Date on which the structure will run out of fuel | [optional] 
**services** | [**\Swagger\Client\Model\V2corporationscorporationIdstructuresServices[]**](V2corporationscorporationIdstructuresServices.md) | Contains a list of service upgrades, and their state | [optional] 
**state_timer_start** | [**\DateTime**](\DateTime.md) | Date at which the structure entered it&#39;s current state | [optional] 
**state_timer_end** | [**\DateTime**](\DateTime.md) | Date at which the structure will move to it&#39;s next state | [optional] 
**unanchors_at** | [**\DateTime**](\DateTime.md) | Date at which the structure will unanchor | [optional] 
**state** | **string** | state string | 
**reinforce_weekday** | **int** | The day of the week when the structure exits its final reinforcement period and becomes vulnerable to attack against its hull. Monday is 0 and Sunday is 6. | 
**reinforce_hour** | **int** | The hour of day that determines the four hour window when the structure will randomly exit its reinforcement periods and become vulnerable to attack against its armor and/or hull. The structure will become vulnerable at a random time that is +/- 2 hours centered on the value of this property. | 
**next_reinforce_weekday** | **int** | The requested change to reinforce_weekday that will take effect at the time shown by next_reinforce_apply. | [optional] 
**next_reinforce_hour** | **int** | The requested change to reinforce_hour that will take effect at the time shown by next_reinforce_apply. | [optional] 
**next_reinforce_apply** | [**\DateTime**](\DateTime.md) | The date and time when the structure&#39;s newly requested reinforcement times (e.g. next_reinforce_hour and next_reinforce_day) will take effect. | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


