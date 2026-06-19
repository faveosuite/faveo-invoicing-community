<?php

namespace App\Http\Controllers\Common;

use Exception;
use Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class CommonMailer
{
    /**
     * @param  array<mixed>  $config
     */
    public function setSmtpDriver(array $config): bool|string
    {
        try {
            if ($config === []) {
                return false;
            }

            $transport = new EsmtpTransport($config['host'], $config['port']);
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);

            // Set the mailer
            Mail::setSymfonyTransport($transport);

            return true;
        } catch (Exception $exception) {
            \Logger::exception($exception);

            return $exception->getMessage();
        }
    }
}
