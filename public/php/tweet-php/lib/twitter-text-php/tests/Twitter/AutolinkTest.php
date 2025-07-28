<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */

/**
 * Twitter Autolink Class Unit Tests.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */
class Twitter_AutolinkTest extends PHPUnit_Framework_TestCase
{
    /**
     * A helper function for providers.
     *
     * @param  string  $test The test to fetch data for.
     * @return array The test data to provide.
     */
    protected function providerHelper($test)
    {
        $data = Spyc::YAMLLoad(DATA.'/autolink.yml');

        return isset($data['tests'][$test]) ? $data['tests'][$test] : [];
    }

    /**
     * @dataProvider  addLinksToUsernamesProvider
     */
    public function testAddLinksToUsernames($description, $text, $expected)
    {
        $linked = Twitter_Autolink::create($text, false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setURLClass('')
      ->addLinksToUsernamesAndLists();
        $this->assertSame($expected, $linked, $description);
    }

    public function addLinksToUsernamesProvider()
    {
        return $this->providerHelper('usernames');
    }

    /**
     * @dataProvider  addLinksToListsProvider
     */
    public function testAddLinksToLists($description, $text, $expected)
    {
        $linked = Twitter_Autolink::create($text, false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setURLClass('')
      ->addLinksToUsernamesAndLists();
        $this->assertSame($expected, $linked, $description);
    }

    public function addLinksToListsProvider()
    {
        return $this->providerHelper('lists');
    }

    /**
     * @dataProvider  addLinksToHashtagsProvider
     */
    public function testAddLinksToHashtags($description, $text, $expected)
    {
        $linked = Twitter_Autolink::create($text, false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setURLClass('')
      ->addLinksToHashtags();
        // XXX: Need to re-order for hashtag as it is written out differently...
        //      We use the same wrapping function for adding links for all methods.
        $linked = preg_replace([
            '!<a class="([^"]*)" href="([^"]*)">([^<]*)</a>!',
            '!title="＃([^"]+)"!',
        ], [
            '<a href="$2" title="$3" class="$1">$3</a>',
            'title="#$1"',
        ], $linked);
        $this->assertSame($expected, $linked, $description);
    }

    public function addLinksToHashtagsProvider()
    {
        return $this->providerHelper('hashtags');
    }

    /**
     * @dataProvider  addLinksToURLsProvider
     */
    public function testAddLinksToURLs($description, $text, $expected)
    {
        $linked = Twitter_Autolink::create($text, false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setURLClass('')
      ->addLinksToURLs();
        $this->assertSame($expected, $linked, $description);
    }

    public function addLinksToURLsProvider()
    {
        return $this->providerHelper('urls');
    }

    /**
     * @dataProvider  addLinksProvider
     */
    public function testAddLinks($description, $text, $expected)
    {
        $linked = Twitter_Autolink::create($text, false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setURLClass('')
      ->addLinks();
        $this->assertSame($expected, $linked, $description);
    }

    public function addLinksProvider()
    {
        return $this->providerHelper('all');
    }
}
