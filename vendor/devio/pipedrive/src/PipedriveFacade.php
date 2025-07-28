<?php

namespace Devio\Pipedrive;

use Illuminate\Support\Facades\Facade;

/**
 * @method static Resources\Activities activities()
 * @method static Resources\ActivityFields activityFields()
 * @method static Resources\ActivityTypes activityTypes()
 * @method static Resources\Authorizations authorizations()
 * @method static Resources\Currencies currencies()
 * @method static Resources\CallLogs callLogs()
 * @method static Resources\DealFields dealFields()
 * @method static Resources\Deals deals()
 * @method static Resources\EmailMessages emailMessages()
 * @method static Resources\EmailThreads emailThreads()
 * @method static Resources\Files files()
 * @method static Resources\Filters filters()
 * @method static Resources\GlobalMessages globalMessages()
 * @method static Resources\Goals goals()
 * @method static Resources\ItemSearches itemSearches()
 * @method static Resources\Leads leads()
 * @method static Resources\NoteFields noteFields()
 * @method static Resources\Notes notes()
 * @method static Resources\OrganizationFields organizationFields()
 * @method static Resources\OrganizationRelationships organizationRelationships()
 * @method static Resources\Organizations organizations()
 * @method static Resources\PermissionSets permissionSets()
 * @method static Resources\PersonFields personFields()
 * @method static Resources\Persons persons()
 * @method static Resources\Pipelines pipelines()
 * @method static Resources\ProductFields productFields()
 * @method static Resources\Products products()
 * @method static Resources\PushNotifications pushNotifications()
 * @method static Resources\Recents recents()
 * @method static Resources\Roles roles()
 * @method static Resources\SearchResults searchResults()
 * @method static Resources\Stages stages()
 * @method static Resources\UserConnections userConnections()
 * @method static Resources\Users users()
 * @method static Resources\UserSettings userSettings()
 * @property-read Resources\Activities $activities
 * @property-read Resources\ActivityFields $activityFields
 * @property-read Resources\ActivityTypes $activityTypes
 * @property-read Resources\Authorizations $authorizations
 * @property-read Resources\CallLogs $callLogs
 * @property-read Resources\Currencies $currencies
 * @property-read Resources\DealFields $dealFields
 * @property-read Resources\Deals $deals
 * @property-read Resources\EmailMessages $emailMessages
 * @property-read Resources\EmailThreads $emailThreads
 * @property-read Resources\Files $files
 * @property-read Resources\Filters $filters
 * @property-read Resources\GlobalMessages $globalMessages
 * @property-read Resources\Goals $goals
 * @property-read Resources\ItemSearches $itemSearches
 * @property-read Resources\Leads $leads
 * @property-read Resources\NoteFields $noteFields
 * @property-read Resources\Notes $notes
 * @property-read Resources\OrganizationFields $organizationFields
 * @property-read Resources\OrganizationRelationships $organizationRelationships
 * @property-read Resources\Organizations $organizations
 * @property-read Resources\PermissionSets $permissionSets
 * @property-read Resources\PersonFields $personFields
 * @property-read Resources\Persons $persons
 * @property-read Resources\Pipelines $pipelines
 * @property-read Resources\ProductFields $productFields
 * @property-read Resources\Products $products
 * @property-read Resources\PushNotifications $pushNotifications
 * @property-read Resources\Recents $recents
 * @property-read Resources\Roles $roles
 * @property-read Resources\SearchResults $searchResults
 * @property-read Resources\Stages $stages
 * @property-read Resources\UserConnections $userConnections
 * @property-read Resources\Users $users
 * @property-read Resources\UserSettings $userSettings
 * @property-read Resources\Webhooks $webhooks
 */
class PipedriveFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pipedrive';
    }
}
