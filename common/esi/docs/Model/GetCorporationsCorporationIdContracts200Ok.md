# GetCorporationsCorporationIdContracts200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**acceptor_id** | **int** | Who will accept the contract | 
**assignee_id** | **int** | ID to whom the contract is assigned, can be corporation or character ID | 
**availability** | **string** | To whom the contract is available | 
**buyout** | **double** | Buyout price (for Auctions only) | [optional] 
**collateral** | **double** | Collateral price (for Couriers only) | [optional] 
**contract_id** | **int** | contract_id integer | 
**date_accepted** | [**\DateTime**](\DateTime.md) | Date of confirmation of contract | [optional] 
**date_completed** | [**\DateTime**](\DateTime.md) | Date of completed of contract | [optional] 
**date_expired** | [**\DateTime**](\DateTime.md) | Expiration date of the contract | 
**date_issued** | [**\DateTime**](\DateTime.md) | Сreation date of the contract | 
**days_to_complete** | **int** | Number of days to perform the contract | [optional] 
**end_location_id** | **int** | End location ID (for Couriers contract) | [optional] 
**for_corporation** | **bool** | true if the contract was issued on behalf of the issuer&#39;s corporation | 
**issuer_corporation_id** | **int** | Character&#39;s corporation ID for the issuer | 
**issuer_id** | **int** | Character ID for the issuer | 
**price** | **double** | Price of contract (for ItemsExchange and Auctions) | [optional] 
**reward** | **double** | Remuneration for contract (for Couriers only) | [optional] 
**start_location_id** | **int** | Start location ID (for Couriers contract) | [optional] 
**status** | **string** | Status of the the contract | 
**title** | **string** | Title of the contract | [optional] 
**type** | **string** | Type of the contract | 
**volume** | **double** | Volume of items in the contract | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


