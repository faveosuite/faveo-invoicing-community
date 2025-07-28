# # MessageObjectAttachments

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The ID of the attachment |
**type** | **string** | The mime-type of the attachment |
**name** | **string** | The name of the attachment | [optional]
**size** | **float** | The size of the attachment | [optional]
**url** | **string** | A URL to the file |
**preview_url** | **string** | A URL to a preview picture of the file | [optional]
**link_expires** | **bool** | If true, it will use the getMessageById endpoint for fetching updated attachment&#39;s urls. Find out more [here](https://pipedrive.readme.io/docs/implementing-messaging-app-extension) | [optional] [default to false]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
