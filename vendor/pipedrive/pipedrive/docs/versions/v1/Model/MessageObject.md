# # MessageObject

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The ID of the message |
**channel_id** | **string** | The channel ID as in the provider |
**sender_id** | **string** | The ID of the provider&#39;s user that sent the message |
**conversation_id** | **string** | The ID of the conversation |
**message** | **string** | The body of the message |
**status** | **string** | The status of the message |
**created_at** | **\DateTime** | The date and time when the message was created in the provider, in UTC. Format: YYYY-MM-DD HH:MM |
**reply_by** | **\DateTime** | The date and time when the message can no longer receive a reply, in UTC. Format: YYYY-MM-DD HH:MM | [optional]
**conversation_link** | **string** | A URL that can open the conversation in the provider&#39;s side | [optional]
**attachments** | [**\Pipedrive\versions\v1\Model\MessageObjectAttachments[]**](MessageObjectAttachments.md) | The list of attachments available in the message | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
