<?php

return [
    /**
     * Zoho datacenter region to use.
     * Available regions: us, eu, in, au, jp, cn.
     */
    'region' => env('CAMPAIGNS_REGION'),

    /**
     * Zoho api client.
     * Run php artisan campaigns:setup and follow the instructions to generate an api client.
     */
    'client_id' => env('CAMPAIGNS_CLIENT_ID'),
    'client_secret' => env('CAMPAIGNS_CLIENT_SECRET'),

    /**
     * The listName to use when no listName has been specified in a method.
     */
    'defaultListName' => 'Faveo Invoicing',

    /**
     * Here you can define properties of the lists.
     */
    'lists' => [

        /**
         * This key is used to identify this list. It can be used
         * as the listName parameter provided in the various methods.
         *
         * You can set it to any string you want and you can add
         * as many lists as you want.
         */
        'subscribers' => [

            /**
             * A Zoho campaigns list key.
             * https://www.zoho.com/campaigns/help/developers/list-management.html
             * You can find this value from Zoho campaigns dashboard under:
             * Contacts > Manage Lists > "Your list" > Setup.
             */
            'listKey' => env('CAMPAIGNS_LIST_KEY'),

        ],
    ],

    /**
     * Define available topics.
     */
    'topics' => [
        'free_products' => [
            'name' => 'Free products',
            'description' => 'Information about free products, free trials, and promotional offers available to users.',
        ],

        'paid_products' => [
            'name' => 'Paid products',
            'description' => 'Updates related to paid products, premium features, pricing changes, upgrades, and purchase-related communications.',
        ],

        'newsletter' => [
            'name' => 'Newsletter',
            'description' => 'Periodic newsletters containing company updates, product highlights, tips, and general announcements.',
        ],
    ],
];
