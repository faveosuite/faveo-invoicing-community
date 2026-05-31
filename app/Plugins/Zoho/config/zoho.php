<?php

return [
    'platforms' => [
        'crm' => [
            'name' => 'Zoho CRM',
            'scope' => [
                'ZohoCRM.modules.ALL',
                'ZohoCRM.settings.ALL',
            ],
            'settings_url' => 'admin/settings/api/zoho',
        ],
        'campaigns' => [
            'name' => 'Zoho Campaigns',
            'scope' => [
                'ZohoCampaigns.campaign.ALL',
                'ZohoCampaigns.contact.ALL',
            ],
            'settings_url' => 'admin/settings/api/zoho',
        ],
    ],

    'default_region' => 'in',
];
