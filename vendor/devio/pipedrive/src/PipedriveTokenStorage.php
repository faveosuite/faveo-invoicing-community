<?php

namespace Devio\Pipedrive;

use Devio\Pipedrive\PipedriveToken;

interface PipedriveTokenStorage
{
    public function setToken(PipedriveToken $token);

    public function getToken();
}
