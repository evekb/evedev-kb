# GetCorporationsCorporationIdIndustryJobs200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**activity_id** | **int** | Job activity ID | 
**blueprint_id** | **int** | blueprint_id integer | 
**blueprint_location_id** | **int** | Location ID of the location from which the blueprint was installed. Normally a station ID, but can also be an asset (e.g. container) or corporation facility | 
**blueprint_type_id** | **int** | blueprint_type_id integer | 
**completed_character_id** | **int** | ID of the character which completed this job | [optional] 
**completed_date** | [**\DateTime**](\DateTime.md) | Date and time when this job was completed | [optional] 
**cost** | **double** | The sume of job installation fee and industry facility tax | [optional] 
**duration** | **int** | Job duration in seconds | 
**end_date** | [**\DateTime**](\DateTime.md) | Date and time when this job finished | 
**facility_id** | **int** | ID of the facility where this job is running | 
**installer_id** | **int** | ID of the character which installed this job | 
**job_id** | **int** | Unique job ID | 
**licensed_runs** | **int** | Number of runs blueprint is licensed for | [optional] 
**location_id** | **int** | ID of the location for the industry facility | 
**output_location_id** | **int** | Location ID of the location to which the output of the job will be delivered. Normally a station ID, but can also be a corporation facility | 
**pause_date** | [**\DateTime**](\DateTime.md) | Date and time when this job was paused (i.e. time when the facility where this job was installed went offline) | [optional] 
**probability** | **float** | Chance of success for invention | [optional] 
**product_type_id** | **int** | Type ID of product (manufactured, copied or invented) | [optional] 
**runs** | **int** | Number of runs for a manufacturing job, or number of copies to make for a blueprint copy | 
**start_date** | [**\DateTime**](\DateTime.md) | Date and time when this job started | 
**status** | **string** | status string | 
**successful_runs** | **int** | Number of successful runs for this job. Equal to runs unless this is an invention job | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


