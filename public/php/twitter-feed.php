<?php
/*
Name: 			Twitter Feed
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version:	6.2.0
*/

require_once 'tweet-php/TweetPHP.php';

// Step 1 - To interact with Twitter’s API you will need to create an API KEY, which you can create at: https://dev.twitter.com/apps

// Step 2 - After creating your API Key you will need to take note of following values: "Consumer key", "Consumer secret", "Access token", "Access token secret" and replace the vars below:

$consumer_key = '1111111111111111111111';
$consumer_secret = '11111111111111111111111111111111111111111';
$access_token = '1111111111-111111111111111111111111111111111111111';
$access_secret = '111111111111111111111111111111111111111111111';

$twitter_screen_name = $_GET['twitter_screen_name'];
$tweets_to_display = (isset($_GET['tweets_to_display']) and $_GET['tweets_to_display'] != '') ? $_GET['tweets_to_display'] : 2;

$TweetPHP = new TweetPHP([
    'consumer_key' => $consumer_key,
    'consumer_secret' => $consumer_secret,
    'access_token' => $access_token,
    'access_token_secret' => $access_secret,
    'twitter_screen_name' => $twitter_screen_name,
    'cache_file' => dirname(__FILE__).'/tweet-php/cache/twitter.txt', // Where on the server to save the cached formatted tweets
    'cache_file_raw' => dirname(__FILE__).'/tweet-php/cache/twitter-array.txt', // Where on the server to save the cached raw tweets
    'cachetime' => 60, // Seconds to cache feed
    'tweets_to_display' => $tweets_to_display, // How many tweets to fetch
    'ignore_replies' => true, // Ignore @replies
    'ignore_retweets' => true, // Ignore retweets
    'twitter_style_dates' => true, // Use twitter style dates e.g. 2 hours ago
    'twitter_date_text' => ['seconds', 'minutes', 'about', 'hour', 'ago'],
    'date_format' => '%I:%M %p %b %d%O', // The defult date format e.g. 12:08 PM Jun 12th. See: http://php.net/manual/en/function.strftime.php
    'date_lang' => null, // Language for date e.g. 'fr_FR'. See: http://php.net/manual/en/function.setlocale.php
    'format' => 'html', // Can be 'html' or 'array'
    'twitter_wrap_open' => '<ul>',
    'twitter_wrap_close' => '</ul>',
    'tweet_wrap_open' => '<li><span class="status"><i class="fab fa-twitter"></i> ',
    'meta_wrap_open' => '</span><span class="meta"> ',
    'meta_wrap_close' => '</span>',
    'tweet_wrap_close' => '</li>',
    'error_message' => 'Oops, our twitter feed is unavailable right now.',
    'error_link_text' => 'Follow us on Twitter',
    'debug' => false,
]);

echo $TweetPHP->get_tweet_list();
