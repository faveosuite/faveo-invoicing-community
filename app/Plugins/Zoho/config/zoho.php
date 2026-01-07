<?php

return [
    'platforms' => [
        'crm' => [
            'name' => 'Zoho CRM',
            'scope' => [
                'ZohoCRM.modules.ALL',
                'ZohoCRM.settings.ALL',
            ],
            'settings_url' => 'zoho/connect',
        ],
        'campaigns' => [
            'name' => 'Zoho Campaigns',
            'scope' => [
                'ZohoCampaigns.campaign.ALL',
                'ZohoCampaigns.contact.ALL',
            ],
            'settings_url' => 'zoho/connect',
        ],
    ],

    'default_region' => 'in',
];
