# GetCharactersCharacterIdChatChannels200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**channel_id** | **int** | Unique channel ID. Always negative for player-created channels. Permanent (CCP created) channels have a positive ID, but don&#39;t appear in the API | 
**name** | **string** | Displayed name of channel | 
**owner_id** | **int** | owner_id integer | 
**comparison_key** | **string** | Normalized, unique string used to compare channel names | 
**has_password** | **bool** | If this is a password protected channel | 
**motd** | **string** | Message of the day for this channel | 
**allowed** | [**\Swagger\Client\Model\V1characterscharacterIdchatChannelsAllowed[]**](V1characterscharacterIdchatChannelsAllowed.md) | allowed array | 
**operators** | [**\Swagger\Client\Model\V1characterscharacterIdchatChannelsOperators[]**](V1characterscharacterIdchatChannelsOperators.md) | operators array | 
**blocked** | [**\Swagger\Client\Model\V1characterscharacterIdchatChannelsBlocked[]**](V1characterscharacterIdchatChannelsBlocked.md) | blocked array | 
**muted** | [**\Swagger\Client\Model\V1characterscharacterIdchatChannelsMuted[]**](V1characterscharacterIdchatChannelsMuted.md) | muted array | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


