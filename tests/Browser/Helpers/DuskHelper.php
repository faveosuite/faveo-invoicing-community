<?php

namespace Tests\Browser\Helpers;

use Laravel\Dusk\Browser;
use Symfony\Component\Console\Output\NullOutput;

trait DuskHelper
{
    protected static $isSetUpCompleted = false;

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

    public function enableRecaptcha($siteKey, $secretKey, string $version = 'v3'): void
    {
        $recaptchaVersions = [
            'v2' => ['recaptcha_status' => true, 'v3_recaptcha_status' => false],
            'v3' => ['recaptcha_status' => false, 'v3_recaptcha_status' => true],
        ];

        $status = $recaptchaVersions[$version] ?? $recaptchaVersions['v3'];

        \App\Model\Common\StatusSetting::first()->update([
            'recaptcha_status' => $status['recaptcha_status'],
            'v3_v2_recaptcha_status' => 1,
            'v3_recaptcha_status' => $status['v3_recaptcha_status'],
        ]);

        \App\ApiKey::first()->update([
            'nocaptcha_sitekey' => $siteKey,
            'captcha_secretCheck' => $secretKey,
        ]);
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

    protected function runDbSetup()
    {
        if (static::$isSetUpCompleted) {
            return;
        }

        $this->dbSetupClean();
        static::$isSetUpCompleted = true;
    }
}
