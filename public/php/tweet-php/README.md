# TweetPHP

A PHP class for fetching tweets from a Twitter user's timeline, and rendering them as an HTML list.

## Features

- Works with Twitter API v1.1
- Tweets are cached to avoid exceeding Twitter’s API request rate limits
- A fallback is provided in case the twitter feed fails to load
- Retweets and @replies can optionally be ignored
- A configuration parameter allows you to specify how many tweets are displayed
- Dates can optionally be displayed in “Twitter style”, e.g. “12 minutes ago”
- You can customize the HTML that wraps your tweets, tweet status and meta information

## Usage

To interact with Twitter's API you will need an API KEY, which you can create at: https://dev.twitter.com/apps

After creating your API Key you will need to take note of following values: "Consumer key", "Consumer secret", "Access token", "Access token secret"

Those values can be passed as options to the class constructor, along with the Twitter screen name you wish to query:

    $TweetPHP = new TweetPHP(array(
      'consumer_key'              => 'xxxxxxxxxxxxxxxxxxxxx',
      'consumer_secret'           => 'xxxxxxxxxxxxxxxxxxxxx',
      'access_token'              => 'xxxxxxxxxxxxxxxxxxxxx',
      'access_token_secret'       => 'xxxxxxxxxxxxxxxxxxxxx',
      'twitter_screen_name'       => 'yourusername'
    ));

Then you can display the results like so:

    echo $TweetPHP->get_tweet_list();

You can also retreive the raw data received from Twitter:

    $tweet_array = $TweetPHP->get_tweet_array();

## Options

Options can be overridden by passing an array of key/value pairs to the class constructor. At a minimum you must set the `consumer_key`, `consumer_secret`, `access_token`, `access_token_secret` and `twitter_screen_name` options, as shown above.

Here is a full list of options, and their default values:

    'consumer_key'          => '',
    'consumer_secret'       => '',
    'access_token'          => '',
    'access_token_secret'   => '',
    'twitter_screen_name'   => '',
    'cache_file'            => dirname(__FILE__) . '/cache/twitter.txt', // Where on the server to save the cached formatted tweets
    'cache_file_raw'        => dirname(__FILE__) . '/cache/twitter-array.txt', // Where on the server to save the cached raw tweets
    'cachetime'             => 60 * 60, // Seconds to cache feed (1 hour).
    'tweets_to_display'     => 10, // How many tweets to fetch
    'ignore_replies'        => true, // Ignore @replies
    'ignore_retweets'       => true, // Ignore retweets
    'twitter_style_dates'   => false, // Use twitter style dates e.g. 2 hours ago
    'twitter_date_text'     => array('seconds', 'minutes', 'about', 'hour', 'ago'),
    'date_format'           => '%I:%M %p %b %d%O', // The defult date format e.g. 12:08 PM Jun 12th. See: http://php.net/manual/en/function.strftime.php
    'date_lang'             => null, // Language for date e.g. 'fr_FR'. See: http://php.net/manual/en/function.setlocale.php
    'format'                => 'html', // Can be 'html' or 'array'
    'twitter_wrap_open'     => '<h2>Latest tweets</h2><ul id="twitter">',
    'twitter_wrap_close'    => '</ul>',
    'tweet_wrap_open'       => '<li><span class="status">',
    'meta_wrap_open'        => '</span><span class="meta"> ',
    'meta_wrap_close'       => '</span>',
    'tweet_wrap_close'      => '</li>',
    'error_message'         => 'Oops, our twitter feed is unavailable right now.',
    'error_link_text'       => 'Follow us on Twitter',
    'debug'                 => false

## Caching

Caching is employed because Twitter rate limits how many times their feeds can be accessed per hour.

When the user timeline is first loaded, the resultant HTML list is saved as a text file on your web server. The default location for this file is: `cache/twitter.txt`

The raw Twitter response is saved as a serialized array in: `cache/twitter-array.txt`

You can change these file paths using the `cache_file` and `cache_file_raw` options. For example, to set a path from your root public directory try:

    $_SERVER['DOCUMENT_ROOT'] . '/path/to/my/cache/dir/filename.txt'

## Debugging

If you are experiencing problems using the plugin please set the `debug` option to `true`. This will set PHP's error reporting level to `E_ALL`, and will also display a debugging report.

You can also fetch the debugging report as an array or HTML list, even when the `debug` option is set to `false`:

    echo $TweetPHP->get_debug_list();
    $debug_array = $TweetPHP->get_debug_array();

## Helper methods

### autolink

Pass raw tweet text to `autolink()` and  it will convert all usernames, hashtags and URLs to HTML links. 

    $autolinked_tweet = autolink($tweet);

This might be handy if you want to process tweets yourself, using the array returned by `get_tweet_array()`.

## Credits

- Feed parsing uses Matt Harris' [tmhOAuth](https://github.com/themattharris/tmhOAuth)
- Hashtag/username parsing uses Mike Cochrane's [twitter-text-php](https://github.com/mikenz/twitter-text-php)
