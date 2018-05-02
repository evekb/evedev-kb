# GetCorporationsCorporationIdOrders200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**duration** | **int** | Number of days for which order is valid (starting from the issued date). An order expires at time issued + duration | 
**escrow** | **double** | For buy orders, the amount of ISK in escrow | [optional] 
**is_buy_order** | **bool** | True if the order is a bid (buy) order | [optional] 
**issued** | [**\DateTime**](\DateTime.md) | Date and time when this order was issued | 
**location_id** | **int** | ID of the location where order was placed | 
**min_volume** | **int** | For buy orders, the minimum quantity that will be accepted in a matching sell order | [optional] 
**order_id** | **int** | Unique order ID | 
**price** | **double** | Cost per unit for this order | 
**range** | **string** | Valid order range, numbers are ranges in jumps | 
**region_id** | **int** | ID of the region where order was placed | 
**type_id** | **int** | The type ID of the item transacted in this order | 
**volume_remain** | **int** | Quantity of items still required or offered | 
**volume_total** | **int** | Quantity of items required or offered at time order was placed | 
**wallet_division** | **int** | The corporation wallet division used for this order. | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


