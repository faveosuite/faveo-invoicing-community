# # LeadResponse

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** | The unique ID of the lead in the UUID format | [optional]
**title** | **string** | The title of the lead | [optional]
**owner_id** | **int** | The ID of the user who owns the lead | [optional]
**creator_id** | **int** | The ID of the user who created the lead | [optional]
**label_ids** | **string[]** | The IDs of the lead labels which are associated with the lead | [optional]
**person_id** | **int** | The ID of a person which this lead is linked to | [optional]
**organization_id** | **int** | The ID of an organization which this lead is linked to | [optional]
**source_name** | **string** | Defines where the lead comes from. Will be &#x60;API&#x60; if the lead was created through the Public API and will be &#x60;Manually created&#x60; if the lead was created manually through the UI. | [optional]
**origin** | **string** | The way this Lead was created. &#x60;origin&#x60; field is set by Pipedrive when Lead is created and cannot be changed. | [optional]
**origin_id** | **string** | The optional ID to further distinguish the origin of the lead - e.g. Which API integration created this Lead. | [optional]
**channel** | **int** | The ID of your Marketing channel this Lead was created from. Recognized Marketing channels can be configured in your &lt;a href&#x3D;\&quot;https://app.pipedrive.com/settings/fields\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;Company settings&lt;/a&gt;. | [optional]
**channel_id** | **string** | The optional ID to further distinguish the Marketing channel. | [optional]
**is_archived** | **bool** | A flag indicating whether the lead is archived or not | [optional]
**was_seen** | **bool** | A flag indicating whether the lead was seen by someone in the Pipedrive UI | [optional]
**value** | [**\Pipedrive\versions\v1\Model\LeadValue**](LeadValue.md) |  | [optional]
**expected_close_date** | **\DateTime** | The date of when the deal which will be created from the lead is expected to be closed. In ISO 8601 format: YYYY-MM-DD. | [optional]
**next_activity_id** | **int** | The ID of the next activity associated with the lead | [optional]
**add_time** | **\DateTime** | The date and time of when the lead was created. In ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ. | [optional]
**update_time** | **\DateTime** | The date and time of when the lead was last updated. In ISO 8601 format: YYYY-MM-DDTHH:MM:SSZ. | [optional]
**visible_to** | [**VisibleTo**](VisibleTo.md) | The visibility of the lead. If omitted, the visibility will be set to the default visibility setting of this item type for the authorized user.&lt;table&gt;&lt;tr&gt;&lt;th&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;1&#x60;&lt;/td&gt;&lt;td&gt;Owner &amp;amp; followers (private)&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;3&#x60;&lt;/td&gt;&lt;td&gt;Entire company (shared)&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; | [optional]
**cc_email** | **string** | The BCC email of the lead | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
