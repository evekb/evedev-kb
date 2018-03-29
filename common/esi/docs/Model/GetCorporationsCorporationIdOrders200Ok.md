# GetCorporationsCorporationIdOrders200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**order_id** | **int** | Unique order ID | 
**type_id** | **int** | The type ID of the item transacted in this order | 
**region_id** | **int** | ID of the region where order was placed | 
**location_id** | **int** | ID of the location where order was placed | 
**range** | **string** | Valid order range, numbers are ranges in jumps | 
**price** | **double** | Cost per unit for this order | 
**volume_total** | **int** | Quantity of items required or offered at time order was placed | 
**volume_remain** | **int** | Quantity of items still required or offered | 
**issued** | [**\DateTime**](\DateTime.md) | Date and time when this order was issued | 
**is_buy_order** | **bool** | True if the order is a bid (buy) order | [optional] 
**min_volume** | **int** | For buy orders, the minimum quantity that will be accepted in a matching sell order | [optional] 
**escrow** | **double** | For buy orders, the amount of ISK in escrow | [optional] 
**duration** | **int** | Number of days for which order is valid (starting from the issued date). An order expires at time issued + duration | 
**wallet_division** | **int** | The corporation wallet division used for this order. | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


