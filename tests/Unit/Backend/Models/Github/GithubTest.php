<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Models\Github;

use App\Model\Github\Github;
use Tests\TestCase;

class GithubTest extends TestCase
{
    public function test_github_table_name(): void
    {
        $this->assertSame('githubs', (new Github())->getTable());
    }

    public function test_github_fillable(): void
    {
        $model = new Github();
        $this->assertContains('client_id', $model->getFillable());
        $this->assertContains('client_secret', $model->getFillable());
        $this->assertContains('username', $model->getFillable());
        $this->assertContains('password', $model->getFillable());
    }

    public function test_github_get_mappings(): void
    {
        $model = new Github();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $this->assertArrayHasKey('client_id', $mappings);
        $this->assertArrayHasKey('client_secret', $mappings);
        $this->assertArrayHasKey('username', $mappings);
        $this->assertArrayHasKey('password', $mappings);
    }

    public function test_github_password_mapping_non_empty(): void
    {
        $model = new Github();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $maskedResult = $mappings['password'][1]('secret');
        $this->assertSame('********', $maskedResult);
    }

    public function test_github_password_mapping_empty(): void
    {
        $model = new Github();
        $ref = new \ReflectionMethod($model, 'getMappings');
        $mappings = $ref->invoke($model);
        $emptyResult = $mappings['password'][1]('');
        $this->assertSame('', $emptyResult);
    }

    public function test_github_password_returns_null_when_raw_null(): void
    {
        $model = new Github();
        $model->setRawAttributes(['password' => null]);
        $this->assertNull($model->password);
    }

    public function test_github_password_encrypts_on_set(): void
    {
        $model = new Github();
        $model->password = 'my-secret';
        $raw = $model->getAttributes()['password'];
        $this->assertNotSame('my-secret', $raw);
        $this->assertNotEmpty($raw);
    }

    public function test_github_password_decrypts_on_get(): void
    {
        $model = new Github();
        $model->password = 'my-secret';
        $this->assertSame('my-secret', $model->password);
    }
}
