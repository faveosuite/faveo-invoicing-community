<?php

namespace Tests\Unit\Backend\Helper;

use Tests\TestCase;

/**
 * Replays recorded inputs/outputs (tests/Unit/Backend/Helper/fixtures) for helpers
 * whose exact output other code depends on, so a refactor can't change it silently.
 */
class HelpersGoldenTest extends TestCase
{
    /** @return array<mixed> */
    private function fixture(string $name): array
    {
        return json_decode((string) file_get_contents(__DIR__.'/fixtures/'.$name.'.json'), associative: true);
    }

    public function test_get_root_url_matches_every_recorded_case(): void
    {
        foreach ($this->fixture('getRootUrl') as [$url, $scheme, $www, $path, $slash, $expected]) {
            $this->assertSame($expected, getRootUrl($url, $scheme, $www, $path, $slash), json_encode([$url, $scheme, $www, $path, $slash]));
        }
    }

    public function test_set_service_config_sets_the_recorded_mail_config(): void
    {
        $fixture = $this->fixture('setServiceConfig');

        foreach ($fixture['cases'] as $name => $case) {
            config($fixture['base']);
            setServiceConfig((object) $case['input']);

            foreach ($case['config'] as $key => $expected) {
                $this->assertSame($expected, config($key), $name.': '.$key);
            }
        }
    }
}
