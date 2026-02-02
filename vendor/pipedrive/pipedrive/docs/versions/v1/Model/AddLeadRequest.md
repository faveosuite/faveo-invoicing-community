# # AddLeadRequest

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**title** | **string** | The name of the lead |
**owner_id** | **int** | The ID of the user which will be the owner of the created lead. If not provided, the user making the request will be used. | [optional]
**label_ids** | **string[]** | The IDs of the lead labels which will be associated with the lead | [optional]
**person_id** | **int** | The ID of a person which this lead will be linked to. If the person does not exist yet, it needs to be created first. This property is required unless &#x60;organization_id&#x60; is specified. | [optional]
**organization_id** | **int** | The ID of an organization which this lead will be linked to. If the organization does not exist yet, it needs to be created first. This property is required unless &#x60;person_id&#x60; is specified. | [optional]
**value** | [**\Pipedrive\versions\v1\Model\LeadValue**](LeadValue.md) |  | [optional]
**expected_close_date** | **\DateTime** | The date of when the deal which will be created from the lead is expected to be closed. In ISO 8601 format: YYYY-MM-DD. | [optional]
**visible_to** | [**VisibleTo**](VisibleTo.md) | The visibility of the lead. If omitted, the visibility will be set to the default visibility setting of this item type for the authorized user. Read more about visibility groups &lt;a href&#x3D;\&quot;https://support.pipedrive.com/en/article/visibility-groups\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;here&lt;/a&gt;.&lt;h4&gt;Light / Growth and Professional plans&lt;/h4&gt;&lt;table&gt;&lt;tr&gt;&lt;th style&#x3D;\&quot;width: 40px\&quot;&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;1&#x60;&lt;/td&gt;&lt;td&gt;Owner &amp;amp; followers&lt;/td&gt;&lt;tr&gt;&lt;td&gt;&#x60;3&#x60;&lt;/td&gt;&lt;td&gt;Entire company&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;&lt;h4&gt;Premium / Ultimate plan&lt;/h4&gt;&lt;table&gt;&lt;tr&gt;&lt;th style&#x3D;\&quot;width: 40px\&quot;&gt;Value&lt;/th&gt;&lt;th&gt;Description&lt;/th&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;1&#x60;&lt;/td&gt;&lt;td&gt;Owner only&lt;/td&gt;&lt;tr&gt;&lt;td&gt;&#x60;3&#x60;&lt;/td&gt;&lt;td&gt;Owner&#39;s visibility group&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;5&#x60;&lt;/td&gt;&lt;td&gt;Owner&#39;s visibility group and sub-groups&lt;/td&gt;&lt;/tr&gt;&lt;tr&gt;&lt;td&gt;&#x60;7&#x60;&lt;/td&gt;&lt;td&gt;Entire company&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt; | [optional]
**was_seen** | **bool** | A flag indicating whether the lead was seen by someone in the Pipedrive UI | [optional]
**origin_id** | **string** | The optional ID to further distinguish the origin of the lead - e.g. Which API integration created this lead. If omitted, &#x60;origin_id&#x60; will be set to null. | [optional]
**channel** | **int** | The ID of Marketing channel this lead was created from. Provided value must be one of the channels configured for your company. You can fetch allowed values with &lt;a href&#x3D;\&quot;https://developers.pipedrive.com/docs/api/v1/DealFields#getDealField\&quot; target&#x3D;\&quot;_blank\&quot; rel&#x3D;\&quot;noopener noreferrer\&quot;&gt;GET /v1/dealFields&lt;/a&gt;. If omitted, channel will be set to null. | [optional]
**channel_id** | **string** | The optional ID to further distinguish the Marketing channel. If omitted, &#x60;channel_id&#x60; will be set to null. | [optional]

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
