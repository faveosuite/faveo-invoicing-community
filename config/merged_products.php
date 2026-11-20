<?php

/*
|--------------------------------------------------------------------------
| Merged products: current product id => retired product id
|--------------------------------------------------------------------------
|
| Faveo v9.4.3.9.B.2 merged each plugin's "Pro" and non-Pro products into one product.
| Each plugin's config.php names both ('product_id' / 'old_product_id'), and the Faveo
| licensing client sends both on license install and verify. A license issued for the
| retired product is moved onto the current one only when the pair it sends is listed
| here: old_product_id is not covered by the request signature, so it is never trusted
| on its own (see App\License\Services\ProductMergeService).
|
*/

return [
    'products' => [
        242 => 241, // AdHocApproval — One-off Approvals
        344 => 343, // AdvanceDashboard
        329 => 328, // AdvancedEncryptionEngine
        244 => 243, // AllianceCRM
        246 => 245, // AttachmentScanner
        248 => 247, // AzureActiveDirectory — Microsoft Entra ID
        254 => 253, // CRTWorkflow
        250 => 249, // Calendar
        357 => 356, // ChangeStatusLock
        252 => 251, // Chat — Third-party Chat Integration
        311 => 312, // Cloner
        256 => 255, // CustomCSS — Portal CSS
        258 => 257, // CustomJs — Portal Scripts
        260 => 259, // CustomLanguage — Text Overrides
        305 => 306, // DataArchive
        262 => 261, // DepartmentStatusLink
        264 => 263, // EleaAssistant
        266 => 265, // Envato
        268 => 267, // Facebook
        270 => 269, // FaveoRedaction
        272 => 271, // Instagram
        335 => 336, // JWTAuth — JWT Sign-in
        274 => 273, // LanguageMigrate — Language Vendor
        276 => 275, // Ldap
        278 => 277, // LimeSurvey
        280 => 279, // Line
        346 => 345, // MicrosoftTeams
        282 => 281, // Migration — osTicket and Spiceworks Migrator
        348 => 347, // MigrationAssistant
        321 => 327, // NextGenTicketing
        309 => 310, // ProductivitySuite
        284 => 283, // RemoteDesktop
        286 => 285, // Reseller
        294 => 293, // SMS
        330 => 331, // ServiceCatalogue
        288 => 287, // ServiceDesk
        290 => 289, // Slack
        292 => 291, // SmartBonding — Cisco Smart Bonding
        296 => 295, // Telephony
        298 => 297, // TicketArchive
        300 => 299, // Twitter — X (Twitter)
        302 => 301, // Whatsapp
        304 => 303, // Zendesk — Zendesk Import
    ],
];
