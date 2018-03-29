# GetCorporationsCorporationIdBlueprints200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**item_id** | **int** | Unique ID for this item. | 
**type_id** | **int** | type_id integer | 
**location_id** | **int** | References a solar system, station or item_id if this blueprint is located within a container. | 
**quantity** | **int** | A range of numbers with a minimum of -2 and no maximum value where -1 is an original and -2 is a copy. It can be a positive integer if it is a stack of blueprint originals fresh from the market (e.g. no activities performed on them yet). | 
**time_efficiency** | **int** | Time Efficiency Level of the blueprint. | 
**material_efficiency** | **int** | Material Efficiency Level of the blueprint. | 
**runs** | **int** | Number of runs remaining if the blueprint is a copy, -1 if it is an original. | 
**location_flag** | **string** | Type of the location_id | 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


