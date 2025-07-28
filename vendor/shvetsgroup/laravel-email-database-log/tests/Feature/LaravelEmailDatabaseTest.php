<?php

namespace ShvetsGroup\LaravelEmailDatabaseLog\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Encoder\Base64Encoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ShvetsGroup\LaravelEmailDatabaseLog\Tests\TestCase;
use ShvetsGroup\LaravelEmailDatabaseLog\Tests\Mail\TestMail;
use ShvetsGroup\LaravelEmailDatabaseLog\Tests\Mail\TestMailWithAttachment;

class LaravelEmailDatabaseTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function the_email_is_logged_to_the_database()
	{
		Mail::to('email@example.com')
			->send(new TestMail());

		$this->assertDatabaseHas('email_log', [
			'date' => now()->format('Y-m-d H:i:s'),
			'from' => 'Example <hello@example.com>',
			'to' => 'email@example.com',
			'cc' => null,
			'bcc' => null,
			'subject' => 'The e-mail subject',
			'body' => '<p>Some random string.</p>',
			'attachments' => null,
		]);
	}

	/** @test */
	public function multiple_recipients_are_comma_separated()
	{
		Mail::to(['email@example.com', 'email2@example.com'])
			->send(new TestMail());

		$this->assertDatabaseHas('email_log', [
			'date' => now()->format('Y-m-d H:i:s'),
			'to' => 'email@example.com, email2@example.com',
			'cc' => null,
			'bcc' => null,
		]);
	}

	/** @test */
	public function recipient_with_name_is_correctly_formatted()
	{
		Mail::to((object)['email' => 'email@example.com', 'name' => 'John Do'])
			->send(new TestMail());

		$this->assertDatabaseHas('email_log', [
			'date' => now()->format('Y-m-d H:i:s'),
			'to' => 'John Do <email@example.com>',
			'cc' => null,
			'bcc' => null,
		]);
	}

	/** @test */
	public function cc_recipient_with_name_is_correctly_formatted()
	{
		Mail::cc((object)['email' => 'email@example.com', 'name' => 'John Do'])
			->send(new TestMail());

		$this->assertDatabaseHas('email_log', [
			'date' => now()->format('Y-m-d H:i:s'),
			'to' => null,
			'cc' => 'John Do <email@example.com>',
			'bcc' => null,
		]);
	}

	/** @test */
	public function bcc_recipient_with_name_is_correctly_formatted()
	{
		Mail::bcc((object)['email' => 'email@example.com', 'name' => 'John Do'])
			->send(new TestMail());

		$this->assertDatabaseHas('email_log', [
			'date' => now()->format('Y-m-d H:i:s'),
			'to' => null,
			'cc' => null,
			'bcc' => 'John Do <email@example.com>',
		]);
	}

	/** @test */
	public function attachement_is_saved()
	{
		Mail::to('email@example.com')->send(new TestMailWithAttachment());

		$log = DB::table('email_log')->first();

		// TODO: Is there a beter way to tests this ?
		$encoded = (new Base64Encoder)->encodeString(file_get_contents(__DIR__ . '/../stubs/demo.txt'));

		$this->assertStringContainsString('Content-Type: text/plain; name=demo.txt', $log->attachments);
		$this->assertStringContainsString('Content-Transfer-Encoding: base64', $log->attachments);
		$this->assertStringContainsString('Content-Disposition: attachment; name=demo.txt; filename=demo.txt', $log->attachments);
		$this->assertStringContainsString($encoded, $log->attachments);
	}
}
