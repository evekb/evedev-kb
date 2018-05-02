# GetCorporationsCorporationIdStarbasesStarbaseIdOk

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**allow_alliance_members** | **bool** | allow_alliance_members boolean | 
**allow_corporation_members** | **bool** | allow_corporation_members boolean | 
**anchor** | **string** | Who can anchor starbase (POS) and its structures | 
**attack_if_at_war** | **bool** | attack_if_at_war boolean | 
**attack_if_other_security_status_dropping** | **bool** | attack_if_other_security_status_dropping boolean | 
**attack_security_status_threshold** | **float** | Starbase (POS) will attack if target&#39;s security standing is lower than this value | [optional] 
**attack_standing_threshold** | **float** | Starbase (POS) will attack if target&#39;s standing is lower than this value | [optional] 
**fuel_bay_take** | **string** | Who can take fuel blocks out of the starbase (POS)&#39;s fuel bay | 
**fuel_bay_view** | **string** | Who can view the starbase (POS)&#39;s fule bay. Characters either need to have required role or belong to the starbase (POS) owner&#39;s corporation or alliance, as described by the enum, all other access settings follows the same scheme | 
**fuels** | [**\Swagger\Client\Model\GetCorporationsCorporationIdStarbasesStarbaseIdOkFuels[]**](GetCorporationsCorporationIdStarbasesStarbaseIdOkFuels.md) | Fuel blocks and other things that will be consumed when operating a starbase (POS) | [optional] 
**offline** | **string** | Who can offline starbase (POS) and its structures | 
**online** | **string** | Who can online starbase (POS) and its structures | 
**unanchor** | **string** | Who can unanchor starbase (POS) and its structures | 
**use_alliance_standings** | **bool** | True if the starbase (POS) is using alliance standings, otherwise using corporation&#39;s | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


