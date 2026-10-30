<?php

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\BillingLog\Model\MailLog;
use App\Model\Common\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DBTestCase;

/** What the admin's demo-request / contact-us email shows: the visitor's page link and their message. */
#[Group('demo')]
class DemoContactEmailTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        Setting::where('id', 1)->update(['sending_status' => 1]);
        Queue::fake(); // the mail log keeps the email body; nothing is really sent
    }

    public static function forms(): array
    {
        return [
            'demo request' => ['demo-request', ['demoname' => 'Jane', 'demoemail' => 'jane@example.com', 'demomessage' => "line one\nline two <b>x</b>", 'demo' => null]],
            'contact us' => ['contact-us', ['conName' => 'Jane', 'email' => 'jane@example.com', 'conmessage' => "line one\nline two <b>x</b>", 'contact' => null]],
        ];
    }

    private function send(string $uri, array $fields, ?string $referer): string
    {
        $honeypot = array_key_last($fields);
        $fields[$honeypot] = ['pot_field' => '', 'time_field' => encrypt(time() - 10)];
        $fields += ['country_code' => '91', 'Mobile' => '9876543210'];

        $before = MailLog::max('id') ?? 0;
        $this->withHeaders(array_filter(['referer' => $referer]))->postJson($uri, $fields)->assertOk();

        return (string) MailLog::where('id', '>', $before)->latest('id')->value('body');
    }

    #[DataProvider('forms')]
    public function test_link_is_the_page_the_visitor_was_on(string $uri, array $fields): void
    {
        $body = $this->send($uri, $fields, url('/pricing?group=2'));

        $this->assertStringContainsString(e(url('/pricing?group=2')), $body);
        $this->assertStringNotContainsString(url($uri), $body);
    }

    #[DataProvider('forms')]
    public function test_link_to_another_site_falls_back_to_home(string $uri, array $fields): void
    {
        $body = $this->send($uri, $fields, 'https://evil.test/x');

        $this->assertStringNotContainsString('evil.test', $body);
    }

    #[DataProvider('forms')]
    public function test_message_keeps_line_breaks_and_html_is_shown_as_text(string $uri, array $fields): void
    {
        $body = $this->send($uri, $fields, referer: null);

        $this->assertStringContainsString("line one<br />\nline two &lt;b&gt;x&lt;/b&gt;", $body);
        $this->assertStringNotContainsString('<b>x</b>', $body);
    }
}
