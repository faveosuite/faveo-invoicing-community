<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Policies;

use App\Policies\Csp\CspPolicy;
use Spatie\Csp\Policy;
use Tests\TestCase;

class CspPolicyTest extends TestCase
{
    public function test_csp_policy_implements_preset(): void
    {
        $this->assertInstanceOf(\Spatie\Csp\Preset::class, new CspPolicy());
    }

    public function test_csp_policy_configure_runs_without_exception_when_not_hot(): void
    {
        \Illuminate\Support\Facades\Vite::shouldReceive('isRunningHot')->andReturn(false);

        $policy = $this->createMock(Policy::class);
        $policy->method('add')->willReturnSelf();
        $policy->method('setReportUri')->willReturnSelf();

        $preset = new CspPolicy();
        $preset->configure($policy);

        $this->assertTrue(true);
    }

    public function test_csp_policy_configure_returns_early_when_vite_hot(): void
    {
        \Illuminate\Support\Facades\Vite::shouldReceive('isRunningHot')->andReturn(true);

        $policy = $this->createMock(Policy::class);
        $policy->expects($this->never())->method('add');

        $preset = new CspPolicy();
        $preset->configure($policy);

        $this->assertTrue(true);
    }
}
