<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */

/**
 * Twitter HitHighlighter Class Unit Tests.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */
class Twitter_HitHighlighterTest extends PHPUnit_Framework_TestCase
{
    /**
     * A helper function for providers.
     *
     * @param  string  $test The test to fetch data for.
     * @return array The test data to provide.
     */
    protected function providerHelper($test)
    {
        $data = Spyc::YAMLLoad(DATA.'/hit_highlighting.yml');

        return isset($data['tests'][$test]) ? $data['tests'][$test] : [];
    }

    /**
     * @dataProvider  addHitHighlightingProvider
     */
    public function testAddHitHighlighting($description, $text, $hits, $expected)
    {
        $extracted = Twitter_HitHighlighter::create($text)->addHitHighlighting($hits);
        $this->assertSame($expected, $extracted, $description);
    }

    public function addHitHighlightingProvider()
    {
        return array_merge($this->providerHelper('plain_text'), $this->providerHelper('with_links'));
    }
}
