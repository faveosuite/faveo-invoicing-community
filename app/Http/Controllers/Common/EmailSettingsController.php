<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Email\EmailSettingRequest;
use App\Model\Common\Setting;
use Config;
use Exception;
use Illuminate\Mail\SentMessage;
use Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Throwable;

class EmailSettingsController extends Controller
{
    protected $emailConfig;

    protected $error;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    protected function checkSConnection(Setting $emailConfig): ?bool
    {
        try {
            $this->emailConfig = $emailConfig;
        } catch (Exception $exception) {
            $this->error = $exception;

            return false;
        }

        return null;
    }

    public function settingsEmail(Setting $settings)
    {
        try {
            $set = $settings->find(1);

            return successResponse('', $set);
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    public function postSettingsEmail(EmailSettingRequest $request)
    {
        try {
            $emailSettings = $request->all();
            $this->emailConfig = Setting::first();

            $this->emailConfig->fill($emailSettings);
            if (! $this->checkSendConnection($this->emailConfig)) {
                return errorResponse($this->errorhandler());
            }

            $this->emailConfig->sending_status = 1;
            $this->emailConfig->save();

            return successResponse(__('message.email_settings_saved'));
        } catch (Exception $exception) {
            return errorResponse($exception->getMessage());
        }
    }

    /**
     * takes care of exception handling in this class.
     *
     *
     * NOTE: to make errors user friendly, more and more cases has to be added to it.
     *
     *
     * @return string returns formatted message
     */
    private function errorhandler()
    {
        return method_exists($this->error, 'getMessage') ? $this->error->getMessage() : $this->error;
    }

    /**
     * checks send connection based on the mail driver.
     *
     *
     * @param  Emails  $emailConfig  emailConfig object
     * @return bool
     */
    protected function checkSendConnection(Setting $emailConfig)
    {
        try {
            $this->emailConfig = $emailConfig;

            //if sending protocol is mail, no connection check is required
            if ($this->emailConfig->driver == 'mail') {
                return $this->checkMailConnection();
            }

            //set outgoing mail configuation to the passed one
            setServiceConfig($this->emailConfig);

            if ($this->emailConfig->driver == 'smtp') {
                return $this->checkSMTPConnection();
            }

            return $this->checkServices();
        } catch (Exception $exception) {
            $this->error = $exception;

            return false;
        }
    }

    /**
     * checks if php's mail function is enabled on current server.
     *
     * @return bool true if enabled else false
     */
    private function checkMailConnection(): bool
    {
        if (function_exists('mail')) {
            return true;
        }

        $this->error = __('message.php_mail_disabled');

        return false;
    }

    /**
     * Checks services status by raw sending mail and waiting for the response.
     *
     * @return SentMessage true if success else false
     */
    private function checkServices()
    {
        try {
            $protocolName = $this->emailConfig->sending_protocol;

            //sending a text message and checking if respond comes. If yes, connection is considered to be successful
            return Mail::raw(sprintf('This is a test mail for successful %s connection', $protocolName), function ($message): void {
                $message->to($this->emailConfig->email_address);
            });
        } catch (Exception $exception) {
            $this->error = $exception;

            return false;
        }
    }

    /**
     * Checks smtp connection stream. If an exception is found, it writes the exception method to $this->error
     * TO DO: it is not required to set email configuration before checking the stream in above method,
     * because it is in this method too.
     *
     * @return bool true if success else false
     */
    private function checkSMTPConnection(): bool
    {
        try {
            $transport = new  EsmtpTransport(Config::get('mail.host'), Config::get('mail.port'));
            $transport->setUsername(Config::get('mail.username'));
            $transport->setPassword(Config::get('mail.password'));

            $transport->start();

            return true;
        } catch (Throwable $throwable) {
            $this->error = $throwable;

            return false;
        }
    }
}
