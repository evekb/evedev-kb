# GetCharactersCharacterIdChatChannels200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**allowed** | [**\Swagger\Client\Model\CharacterscharacterIdchatChannelsAllowed[]**](CharacterscharacterIdchatChannelsAllowed.md) | allowed array | 
**blocked** | [**\Swagger\Client\Model\CharacterscharacterIdchatChannelsBlocked[]**](CharacterscharacterIdchatChannelsBlocked.md) | blocked array | 
**channel_id** | **int** | Unique channel ID. Always negative for player-created channels. Permanent (CCP created) channels have a positive ID, but don&#39;t appear in the API | 
**comparison_key** | **string** | Normalized, unique string used to compare channel names | 
**has_password** | **bool** | Whether this is a password protected channel | 
**motd** | **string** | Message of the day for this channel | 
**muted** | [**\Swagger\Client\Model\CharacterscharacterIdchatChannelsMuted[]**](CharacterscharacterIdchatChannelsMuted.md) | muted array | 
**name** | **string** | Displayed name of channel | 
**operators** | [**\Swagger\Client\Model\CharacterscharacterIdchatChannelsOperators[]**](CharacterscharacterIdchatChannelsOperators.md) | operators array | 
**owner_id** | **int** | owner_id integer | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


