# GetCorporationsCorporationIdWalletsDivisionJournal200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**amount** | **float** | Transaction amount. Positive when value transferred to the first party. Negative otherwise | [optional] 
**balance** | **float** | Wallet balance after transaction occurred | [optional] 
**date** | [**\DateTime**](\DateTime.md) | Date and time of transaction | 
**extra_info** | [**\Swagger\Client\Model\V1corporationscorporationIdwalletsdivisionjournalExtraInfo**](V1corporationscorporationIdwalletsdivisionjournalExtraInfo.md) |  | [optional] 
**first_party_id** | **int** | first_party_id integer | [optional] 
**first_party_type** | **string** | first_party_type string | [optional] 
**reason** | **string** | reason string | [optional] 
**ref_id** | **int** | Unique journal reference ID | 
**ref_type** | **string** | Transaction type, different type of transaction will populate different fields in &#x60;extra_info&#x60; | 
**second_party_id** | **int** | second_party_id integer | [optional] 
**second_party_type** | **string** | second_party_type string | [optional] 
**tax** | **float** | Tax amount received for tax related transactions | [optional] 
**tax_reciever_id** | **int** | the corporation ID receiving any tax paid | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


