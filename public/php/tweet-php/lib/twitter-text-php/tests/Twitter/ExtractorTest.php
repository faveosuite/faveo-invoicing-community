<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */

/**
 * Twitter Extractor Class Unit Tests.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */
class Twitter_ExtractorTest extends PHPUnit_Framework_TestCase
{
    /**
     * A helper function for providers.
     *
     * @param  string  $test The test to fetch data for.
     * @return array The test data to provide.
     */
    protected function providerHelper($test)
    {
        $data = Spyc::YAMLLoad(DATA.'/extract.yml');

        return isset($data['tests'][$test]) ? $data['tests'][$test] : [];
    }

    /**
     * @dataProvider  extractMentionedUsernamesProvider
     */
    public function testExtractMentionedUsernames($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractMentionedUsernames();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractMentionedUsernamesProvider()
    {
        return $this->providerHelper('mentions');
    }

    /**
     * @dataProvider  extractRepliedUsernamesProvider
     */
    public function testExtractRepliedUsernames($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractRepliedUsernames();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractRepliedUsernamesProvider()
    {
        return $this->providerHelper('replies');
    }

    /**
     * @dataProvider  extractURLsProvider
     */
    public function testExtractURLs($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractURLs();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractURLsProvider()
    {
        return $this->providerHelper('urls');
    }

    /**
     * @dataProvider  extractHashtagsProvider
     */
    public function testExtractHashtags($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractHashtags();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractHashtagsProvider()
    {
        return $this->providerHelper('hashtags');
    }

    /**
     * @dataProvider  extractHashtagsWithIndicesProvider
     */
    public function testExtractHashtagsWithIndices($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractHashtagsWithIndices();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractHashtagsWithIndicesProvider()
    {
        return $this->providerHelper('hashtags_with_indices');
    }

    /**
     * @dataProvider  extractURLsWithIndicesProvider
     */
    public function testExtractURLsWithIndices($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractURLsWithIndices();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractURLsWithIndicesProvider()
    {
        return $this->providerHelper('urls_with_indices');
    }

    /**
     * @dataProvider  extractMentionedUsernamesWithIndicesProvider
     */
    public function testExtractMentionedUsernamesWithIndices($description, $text, $expected)
    {
        $extracted = Twitter_Extractor::create($text)->extractMentionedUsernamesWithIndices();
        $this->assertSame($expected, $extracted, $description);
    }

    public function extractMentionedUsernamesWithIndicesProvider()
    {
        return $this->providerHelper('mentions_with_indices');
    }
}
