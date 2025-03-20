# # BaseMailThreadMessagesAllOf

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**from** | [**\Pipedrive\versions\v1\Model\MailThreadParticipant[]**](MailThreadParticipant.md) | Senders of the mail thread | [optional]
**to** | [**\Pipedrive\versions\v1\Model\MailThreadParticipant[]**](MailThreadParticipant.md) | Recipients of the mail thread | [optional]
**cc** | [**\Pipedrive\versions\v1\Model\MailThreadParticipant[]**](MailThreadParticipant.md) | Participants of the Cc | [optional]
**bcc** | [**\Pipedrive\versions\v1\Model\MailThreadParticipant[]**](MailThreadParticipant.md) | Participants of the Bcc | [optional]
**body_url** | **string** | A link to the mail thread message | [optional]
**mail_thread_id** | **int** | ID of the mail thread | [optional]
**draft** | **string** | If the mail message has a draft status then the value is the mail message object as JSON formatted string, otherwise &#x60;null&#x60;. | [optional]
**has_body_flag** | [**NumberBooleanDefault0**](NumberBooleanDefault0.md) | Whether the mail thread message has a body | [optional]
**sent_flag** | [**NumberBooleanDefault0**](NumberBooleanDefault0.md) | Whether the mail thread message is sent | [optional]
**sent_from_pipedrive_flag** | [**NumberBooleanDefault0**](NumberBooleanDefault0.md) | Whether the mail thread message is sent from Pipedrive | [optional]
**message_time** | **\DateTime** | The time when the mail message was received or created | [optional]
**add_time** | **\DateTime** | The time when the mail message was inserted to database | [optional]
**update_time** | **\DateTime** | The time when the mail message was updated in database received | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
