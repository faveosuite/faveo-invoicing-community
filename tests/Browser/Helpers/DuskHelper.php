<?php

namespace Tests\Browser\Helpers;

use App\ApiKey;
use App\Http\Controllers\Common\EmailSettingsController;
use App\Http\Requests\Email\EmailSettingRequest;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use Illuminate\Http\Request;
use Laravel\Dusk\Browser;
use Symfony\Component\Console\Output\NullOutput;

trait DuskHelper
{
    protected static $isSetUpCompleted = false;


    protected function configs(): array
    {
        return [
            'email' => [
                'from_name' => 'My Testing',
                'driver' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'email' => '',
                'password' => '',
            ],
            'msg91' => [
                'msg91_auth_key' => '',
                'msg91_sender' => '',
                'msg91_template_id' => '',
            ],
            'v3_recaptcha' => [
                'nocaptcha_sitekey' => '',
                'captcha_secretCheck' => '',
            ],
            'v2_recaptcha' => [
                'nocaptcha_sitekey' => '',
                'captcha_secretCheck' => '',
            ],
        ];
    }


    public function visitLoginPage(Browser $browser): void
    {
        $browser->visit('/login');
        $this->bypassInsecurePage($browser);
    }

    public function bypassInsecurePage(Browser $browser): void
    {
        try {
            $browser->click('#details-button')
                ->click('#proceed-link');
        } catch (\Exception $e) {
            // Silently ignore if the warning does not exist
        }
    }

    public function loginAsUser(Browser $browser, string $email, string $password): void
    {
        $this->visitLoginPage($browser);
        $browser->pause(1000);
        $browser->type('#username', $email)
            ->type('#pass', $password)
            ->press('#login-btn');
    }

    public function enableRecaptcha(string $version = 'v3'): void
    {
        $settings = $this->recaptchaSettings($version);

        StatusSetting::first()?->update([
            'recaptcha_status'        => $settings['recaptcha_status'],
            'v3_recaptcha_status'     => $settings['v3_recaptcha_status'],
            'v3_v2_recaptcha_status'  => 1,
        ]);

        \App\ApiKey::first()?->update([
            'nocaptcha_sitekey'       => $settings['site_key'],
            'captcha_secretCheck'     => $settings['secret_key'],
        ]);
    }

    public function disableRecaptcha(): void
    {
        StatusSetting::first()?->update([
            'recaptcha_status'        => 0,
            'v3_recaptcha_status'     => 0,
            'v3_v2_recaptcha_status'  => 0,
        ]);
    }

    protected function recaptchaSettings(string $version): array
    {
        $config = $this->configs();

        return match ($version) {
            'v2' => [
                'recaptcha_status' => true,
                'v3_recaptcha_status' => false,
                'site_key' => $config['v2_recaptcha']['nocaptcha_sitekey'] ?? null,
                'secret_key' => $config['v2_recaptcha']['captcha_secretCheck'] ?? null,
            ],
            default => [
                'recaptcha_status' => false,
                'v3_recaptcha_status' => true,
                'site_key' => $config['v3_recaptcha']['nocaptcha_sitekey'] ?? null,
                'secret_key' => $config['v3_recaptcha']['captcha_secretCheck'] ?? null,
            ],
        };
    }

    public function showCaption(Browser $browser, string $message): void
    {
        $escapedMessage = addslashes($message);

        $browser->script("
        document.body.insertAdjacentHTML('beforeend',
            `<div id=\"dusk-caption\" 
                style=\"
                    position:fixed;
                    bottom:20px;
                    left:20px;
                    background:#e53e3e;
                    color:#fefcbf;
                    padding:10px 20px;
                    z-index:9999;
                    font-size:16px;
                    font-weight:bold;
                    border:2px solid #fef08a;
                    border-radius:8px;
                    box-shadow:0 0 10px #fef08a;
                    font-family: sans-serif;
                \">
                ${escapedMessage}
            </div>`
        );
    ");
    }

    protected function dbSetupClean()
    {
        ob_start();
        \Artisan::call('testing-setup', [], new NullOutput);
        ob_end_clean();
    }

    protected function runDbSetup(): void
    {
        if (static::$isSetUpCompleted) {
            return;
        }

        $this->dbSetupClean();
        static::$isSetUpCompleted = true;
    }

    protected function setUpEmail(bool $enable = true): void
    {
        $request = EmailSettingRequest::create(
            '/settings/email',
            Request::METHOD_POST,
            $enable ? $this->configs()['email'] : [
                'driver' => 'log',
                'host' => null,
                'port' => null,
                'encryption' => null,
                'email' => null,
                'password' => null,
            ]
        );

        (new EmailSettingsController())->postSettingsEmail($request);
    }

    protected function setUpMobile(bool $enable = true): void
    {
        if (!$setting = ApiKey::first()) {
            return;
        }

        $setting->update(match ($enable) {
            true => $this->configs()['msg91'],
            false => array_fill_keys(['msg91_auth_key', 'msg91_sender', 'msg91_template_id'], null),
        });
    }

    protected function enableEmailAndMobile($email = true, $mobile = true): void
    {
        $this->setUpEmail(true);
        StatusSetting::first()->update([
            'emailverification_status' => $email ? 1 : 0,
        ]);

        $this->setUpMobile(true);
        StatusSetting::first()->update([
            'msg91_status' => $mobile ? 1 : 0,
        ]);
    }

    protected function enableOrDisableTerms($enable = true): void
    {
        StatusSetting::first()->update([
            'terms' => $enable ? 1 : 0,
        ]);
    }
}