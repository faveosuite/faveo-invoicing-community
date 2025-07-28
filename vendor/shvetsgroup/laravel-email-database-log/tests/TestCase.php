<?php

namespace ShvetsGroup\LaravelEmailDatabaseLog\Tests;

use ShvetsGroup\LaravelEmailDatabaseLog\LaravelEmailDatabaseLogServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
	public function getEnvironmentSetUp($app)
	{
		// import the migrations
		include_once __DIR__ . '/../src/database/migrations/2015_07_31_1_email_log.php';
		include_once __DIR__ . '/../src/database/migrations/2016_09_21_001638_add_bcc_column_email_log.php';
		include_once __DIR__ . '/../src/database/migrations/2017_11_10_001638_add_more_mail_columns_email_log.php';
		include_once __DIR__ . '/../src/database/migrations/2018_05_11_115355_use_longtext_for_attachments.php';

		// run the up() method of those migration classes
		(new \EmailLog)->up();
		(new \AddBccColumnEmailLog)->up();
		(new \AddMoreMailColumnsEmailLog)->up();
		(new \UseLongtextForAttachments)->up();
	}

	protected function getPackageProviders($app)
	{
		return [
			LaravelEmailDatabaseLogServiceProvider::class,
		];
	}
}
