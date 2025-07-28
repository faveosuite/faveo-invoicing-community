<?php

 /**
  * TweetPHP.
  *
  * @author Jonathan Nicol @f6design
  *
  * @version 1.0.6
  *
  * @license The MIT License http://opensource.org/licenses/mit-license.php
  *
  * @link  http://f6design.com/journal/2013/06/20/tweetphp-display-tweets-on-your-website-using-php/
  *
  * Notes:
  * To interact with Twitter's API you will need to create an API KEY:
  * https://dev.twitter.com/apps
  * After creating your API Key you will need to pass the following values to the class
  * constructor: "Consumer key", "Consumer secret", "Access token", "Access token secret"
  * --
  * Options can be overridden by passing an array of key/value pairs to the class
  * constructor. At a minimum you must set the consumer_key, consumer_secret, access_token,
  * access_token_secret and twitter_screen_name options.
  * --
  * You may also need to change the cache_file option to point at a directory/file on your
  * web server. Caching is employed because Twitter rate limits how many times their feeds
  * can be accessed per hour.
  *
  * Credits:
  * Feed parsing: https://github.com/themattharris/tmhOAuth
  * Hashtag/username parsing: https://github.com/mikenz/twitter-text-php
  */
 class TweetPHP
 {
     private $tmhOAuth;

     private $options;

     private $tweet_found = false;

     private $tweet_count = 0;

     private $tweet_list;

     private $tweet_array;

     private $debug_report = [];

     /**
      * Initialize a new TweetPHP object.
      */
     public function __construct($options = [])
     {
         $twitterDetails=App\ApiKey::find(1)->pluck('consumer_key','consumer_secret','access_token','token_secret');
         $this->options = array_merge(
      [
          'consumer_key' => '',
          'consumer_secret' => '',
          'access_token' => '',
          'access_token_secret' => '',
          'twitter_screen_name' => '',
          'cache_file' => dirname(__FILE__).'/cache/twitter.txt', // Where on the server to save the cached formatted tweets
          'cache_file_raw' => dirname(__FILE__).'/cache/twitter-array.txt', // Where on the server to save the cached raw tweets
          'cachetime' => 60 * 60, // Seconds to cache feed (1 hour).
          'tweets_to_display' => 10, // How many tweets to fetch
          'ignore_replies' => true, // Ignore @replies
          'ignore_retweets' => true, // Ignore retweets
          'twitter_style_dates' => false, // Use twitter style dates e.g. 2 hours ago
          'twitter_date_text' => ['seconds', 'minutes', 'about', 'hour', 'ago'],
          'date_format' => '%I:%M %p %b %e%O', // The defult date format e.g. 12:08 PM Jun 12th. See: http://php.net/manual/en/function.strftime.php
          'date_lang' => null, // Language for date e.g. 'fr_FR'. See: http://php.net/manual/en/function.setlocale.php
          'format' => 'html', // Can be 'html' or 'array'
          'twitter_wrap_open' => '<h2>Latest tweets</h2><ul id="twitter">',
          'twitter_wrap_close' => '</ul>',
          'tweet_wrap_open' => '<li><span class="status">',
          'meta_wrap_open' => '</span><span class="meta"> ',
          'meta_wrap_close' => '</span>',
          'tweet_wrap_close' => '</li>',
          'error_message' => 'Oops, our twitter feed is unavailable right now.',
          'error_link_text' => 'Follow us on Twitter',
          'debug' => false,
      ],
        $options
      );

         if ($this->options['debug']) {
             error_reporting(E_ALL);
         }

         if ($this->options['date_lang']) {
             setlocale(LC_ALL, $this->options['date_lang']);
         }

         $cache_file_timestamp = ((file_exists($this->options['cache_file']))) ? filemtime($this->options['cache_file']) : 0;
         $this->add_debug_item('Cache expiration timestamp: '.(time() - $this->options['cachetime']));
         $this->add_debug_item('Cache file timestamp: '.$cache_file_timestamp);

         // Show file from cache if still valid.
         if (time() - $this->options['cachetime'] < $cache_file_timestamp) {
             $this->tweet_found = true;
             $this->add_debug_item('Cache file is newer than cachetime.');
             $this->tweet_list = file_get_contents($this->options['cache_file']);
             $this->tweet_array = unserialize(file_get_contents($this->options['cache_file_raw']));
         } else {
             $this->add_debug_item("Cache file doesn't exist or is older than cachetime.");
             $this->fetch_tweets();
         }

         // In case the feed did not parse or load correctly, show a link to the Twitter account.
         if (! $this->tweet_found) {
             $this->add_debug_item('No tweets were found. error_message will be displayed.');
             $this->tweet_list = $this->options['twitter_wrap_open'].$this->options['tweet_wrap_open'].$this->options['error_message'].' '.$this->options['meta_wrap_open'].'<a href="http://twitter.com/'.$this->options['twitter_screen_name'].'">'.$this->options['error_link_text'].'</a>'.$this->options['meta_wrap_close'].$this->options['tweet_wrap_close'].$this->options['twitter_wrap_close'];
             $this->tweet_array = ['Error fetching or loading tweets'];
         }
     }

     /**
      * Fetch tweets using Twitter API.
      */
     private function fetch_tweets()
     {
         $this->add_debug_item('Fetching fresh tweets using Twitter API.');

         require_once dirname(__FILE__).'/lib/tmhOAuth/tmhOAuth.php';

         // Creates a tmhOAuth object.
         $this->tmhOAuth = new tmhOAuth([
             'consumer_key' => $this->options['consumer_key'],
             'consumer_secret' => $this->options['consumer_secret'],
             'token' => $this->options['access_token'],
             'secret' => $this->options['access_token_secret'],
         ]);

         // Request Twitter timeline.
         $params = [
             'screen_name' => $this->options['twitter_screen_name'],
         ];
         if ($this->options['ignore_retweets']) {
             $params['include_rts'] = 'false';
         }
         if ($this->options['ignore_replies']) {
             $params['exclude_replies'] = 'true';
         }
         $response_code = $this->tmhOAuth->request('GET', $this->tmhOAuth->url('1.1/statuses/user_timeline.json'), $params);

         $this->add_debug_item('tmhOAuth response code: '.$response_code);

         if ($response_code == 200) {
             $data = json_decode($this->tmhOAuth->response['response'], true);

             // Open the twitter wrapping element.
             $html = $this->options['twitter_wrap_open'];

             // Iterate over tweets.
             foreach ($data as $tweet) {
                 $html .= $this->parse_tweet($tweet);
                 // If we have processed enough tweets, stop.
                 if ($this->tweet_count >= $this->options['tweets_to_display']) {
                     break;
                 }
             }

             // Close the twitter wrapping element.
             $html .= $this->options['twitter_wrap_close'];

             // Save the formatted tweet list to a file.
             $file = fopen($this->options['cache_file'], 'w');
             fwrite($file, $html);
             fclose($file);

             // Save the raw data array to a file.
             $file = fopen($this->options['cache_file_raw'], 'w');
             fwrite($file, serialize($data));
             fclose($file);

             $this->tweet_list = $html;
             $this->tweet_array = $data;
         } else {
             $this->add_debug_item('Bad tmhOAuth response code.');
         }
     }

     /**
      * Parse an individual tweet.
      */
     private function parse_tweet($tweet)
     {
         $this->tweet_found = true;
         $this->tweet_count++;

         // Format tweet text
         $tweet_text_raw = $tweet['text'];
         $tweet_text = $this->autolink($tweet_text_raw);

         // Tweet date is in GMT. Convert to UNIX timestamp in the local time of the tweeter.
         $utc_offset = $tweet['user']['utc_offset'];
         $tweet_time = strtotime($tweet['created_at']) + $utc_offset;

         if ($this->options['twitter_style_dates']) {
             // Convert tweet timestamp into Twitter style date ("About 2 hours ago")
             $current_time = time();
             $time_diff = abs($current_time - $tweet_time);
             switch ($time_diff) {
          case $time_diff < 60:
            $display_time = $time_diff.' '.$this->options['twitter_date_text'][0].' '.$this->options['twitter_date_text'][4];
            break;
          case $time_diff >= 60 && $time_diff < 3600:
            $min = floor($time_diff / 60);
            $display_time = $min.' '.$this->options['twitter_date_text'][1].' '.$this->options['twitter_date_text'][4];
            break;
          case $time_diff >= 3600 && $time_diff < 86400:
            $hour = floor($time_diff / 3600);
            $display_time = $this->options['twitter_date_text'][2].' '.$hour.' '.$this->options['twitter_date_text'][3];
            if ($hour > 1) {
                $display_time .= 's';
            }
            $display_time .= ' '.$this->options['twitter_date_text'][4];
            break;
          default:
            $format = str_replace('%O', date('S', $tweet_time), $this->options['date_format']);
            $display_time = strftime($format, $tweet_time);
            break;
        }
         } else {
             $format = str_replace('%O', date('S', $tweet_time), $this->options['date_format']);
             $display_time = strftime($format, $tweet_time);
         }

         $href = 'http://twitter.com/'.$tweet['user']['screen_name'].'/status/'.$tweet['id_str'];

         return $this->options['tweet_wrap_open'].$tweet_text.$this->options['meta_wrap_open'].'<a href="'.$href.'">'.$display_time.'</a>'.$this->options['meta_wrap_close'].$this->options['tweet_wrap_close'];
     }

     /**
      * Add a debuggin item.
      */
     private function add_debug_item($msg)
     {
         array_push($this->debug_report, $msg);
     }

     /**
      * Get debugging information as an HTML list.
      */
     public function get_debug_list()
     {
         $debug_list = '<ul>';
         foreach ($this->debug_report as $debug_item) {
             $debug_list .= '<li>'.$debug_item.'</li>';
         }
         $debug_list .= '</ul>';

         return $debug_list;
     }

     /**
      * Get debugging information as an array.
      */
     public function get_debug_array()
     {
         return $this->debug_report;
     }

     /**
      * Helper function to convert usernames, hashtags and URLs
      * in a tweet to HTML links.
      */
     public function autolink($tweet)
     {
         require_once dirname(__FILE__).'/lib/twitter-text-php/lib/Twitter/Autolink.php';

         $autolinked_tweet = Twitter_Autolink::create($tweet, false)
        ->setNoFollow(false)->setExternal(false)->setTarget('')
        ->setUsernameClass('')
        ->setHashtagClass('')
        ->setURLClass('')
        ->addLinks();

         return $autolinked_tweet;
     }

     /**
      * Get tweets as HTML list.
      */
     public function get_tweet_list()
     {
         if ($this->options['debug']) {
             return $this->get_debug_list().$this->tweet_list;
         } else {
             return $this->tweet_list;
         }
     }

     /**
      * Get tweets as an array.
      */
     public function get_tweet_array()
     {
         return $this->tweet_array;
     }
 }
