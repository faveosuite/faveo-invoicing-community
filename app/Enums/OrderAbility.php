<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Everything a customer can do with an order. Each value is the name of the
 * OrderPolicy method that decides it — pass the case to Gate, never a bare string.
 */
enum OrderAbility: string
{
    case Renew = 'renew';
    case AdminRenew = 'adminRenew';
    case Download = 'download';
    case DownloadVersion = 'downloadVersion';
    case Deploy = 'deploy';
    case DeleteCloud = 'deleteCloud';
    case ManageCloud = 'manageCloud';
    case ChangeCloudPlan = 'changeCloudPlan';
    case ChangeCloudAgents = 'changeCloudAgents';
    case EnableAutoRenew = 'enableAutoRenew';
    case Reissue = 'reissue';
    case UseLicenseFile = 'useLicenseFile';
}
