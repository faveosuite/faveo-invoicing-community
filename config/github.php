<?php

return [
    /*
     * The GitHub owner (org or user) and repo name for this billing application.
     * Used when creating tags and releases via the admin panel.
     */
    'owner'         => env('GITHUB_REPO_OWNER', 'faveosuite'),
    'repo'          => env('GITHUB_REPO_NAME', 'faveo-helpdesk-advance'),
    'workflow_file' => env('GITHUB_WORKFLOW_FILE', 'release.yml'),
];
