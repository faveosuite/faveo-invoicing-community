# # ProductFileItem

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **int** | The ID of the file | [optional]
**product_id** | **int** | The ID of the product associated with the file | [optional]
**add_time** | **string** | The UTC date time when the file was uploaded. Format: YYYY-MM-DD HH:MM:SS | [optional]
**update_time** | **string** | The UTC date time when the file was last updated. Format: YYYY-MM-DD HH:MM:SS | [optional]
**file_name** | **string** | The original name of the file | [optional]
**file_size** | **int** | The size of the file in bytes | [optional]
**active_flag** | **bool** | Whether the user is active or not. | [optional]
**inline_flag** | **bool** | Whether the file was uploaded as inline or not | [optional]
**remote_location** | **string** | The location type to send the file to. Only googledrive is supported at the moment. | [optional]
**remote_id** | **string** | The ID of the remote item | [optional]
**s3_bucket** | **string** | The location of the cloud storage | [optional]
**product_name** | **string** | The name of the product associated with the file | [optional]
**url** | **string** | The URL to download the file | [optional]
**name** | **string** | The visible name of the file | [optional]
**description** | **string** | The description of the file | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
