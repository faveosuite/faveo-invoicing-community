<?php

declare(strict_types=1);

return [
    'platforms' => [
        'crm' => [
            'name' => 'Zoho CRM',
            'scope' => [
                'ZohoCRM.modules.ALL',
                'ZohoCRM.settings.ALL',
            ],
            'settings_url' => 'admin/settings/api/zoho/crm',
        ],
        'campaigns' => [
            'name' => 'Zoho Campaigns',
            'scope' => [
                'ZohoCampaigns.campaign.ALL',
                'ZohoCampaigns.contact.ALL',
            ],
            'settings_url' => 'admin/settings/api/zoho/campaigns',
        ],
    ],

    'default_region' => 'in',
];
