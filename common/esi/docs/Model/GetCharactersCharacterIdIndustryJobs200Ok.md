# GetCharactersCharacterIdIndustryJobs200Ok

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**job_id** | **int** | Unique job ID | 
**installer_id** | **int** | ID of the character which installed this job | 
**facility_id** | **int** | ID of the facility where this job is running | 
**station_id** | **int** | ID of the station where industry facility is located | 
**activity_id** | **int** | Job activity ID | 
**blueprint_id** | **int** | blueprint_id integer | 
**blueprint_type_id** | **int** | blueprint_type_id integer | 
**blueprint_location_id** | **int** | Location ID of the location from which the blueprint was installed. Normally a station ID, but can also be an asset (e.g. container) or corporation facility | 
**output_location_id** | **int** | Location ID of the location to which the output of the job will be delivered. Normally a station ID, but can also be a corporation facility | 
**runs** | **int** | Number of runs for a manufacturing job, or number of copies to make for a blueprint copy | 
**cost** | **double** | The sume of job installation fee and industry facility tax | [optional] 
**licensed_runs** | **int** | Number of runs blueprint is licensed for | [optional] 
**probability** | **float** | Chance of success for invention | [optional] 
**product_type_id** | **int** | Type ID of product (manufactured, copied or invented) | [optional] 
**status** | **string** | status string | 
**duration** | **int** | Job duration in seconds | 
**start_date** | [**\DateTime**](\DateTime.md) | Date and time when this job started | 
**end_date** | [**\DateTime**](\DateTime.md) | Date and time when this job finished | 
**pause_date** | [**\DateTime**](\DateTime.md) | Date and time when this job was paused (i.e. time when the facility where this job was installed went offline) | [optional] 
**completed_date** | [**\DateTime**](\DateTime.md) | Date and time when this job was completed | [optional] 
**completed_character_id** | **int** | ID of the character which completed this job | [optional] 
**successful_runs** | **int** | Number of successful runs for this job. Equal to runs unless this is an invention job | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


