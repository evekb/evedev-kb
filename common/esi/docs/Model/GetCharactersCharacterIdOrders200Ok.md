# GetCharactersCharacterIdOrders200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**account_id** | **int** | Wallet division for the buyer or seller of this order. Always 1000 for characters. Currently 1000 through 1006 for corporations | 
**duration** | **int** | Numer of days for which order is valid (starting from the issued date). An order expires at time issued + duration | 
**escrow** | **float** | For buy orders, the amount of ISK in escrow | 
**is_buy_order** | **bool** | True for a bid (buy) order. False for an offer (sell) order | 
**is_corp** | **bool** | is_corp boolean | 
**issued** | [**\DateTime**](\DateTime.md) | Date and time when this order was issued | 
**location_id** | **int** | ID of the location where order was placed | 
**min_volume** | **int** | For bids (buy orders), the minimum quantity that will be accepted in a matching offer (sell order) | 
**order_id** | **int** | Unique order ID | 
**price** | **float** | Cost per unit for this order | 
**range** | **string** | Valid order range, numbers are ranges in jumps | 
**region_id** | **int** | ID of the region where order was placed | 
**state** | **string** | Current order state | 
**type_id** | **int** | The type ID of the item transacted in this order | 
**volume_remain** | **int** | Quantity of items still required or offered | 
**volume_total** | **int** | Quantity of items required or offered at time order was placed | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


