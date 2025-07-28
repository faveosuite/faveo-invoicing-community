<?php

namespace App\Http\Controllers\Common;

use Exception;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class CommonMailer
{
    public function setSmtpDriver($config)
    {
        try {
            if (! $config) {
                return false;
            }
            $transport = new EsmtpTransport($config['host'], $config['port']);
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);

            // Set the mailer
            \Mail::setSymfonyTransport($transport);

            return true;
        } catch (Exception $e) {
            loging($e->getMessage());

            return $e->getMessage();
        }
    }
}
