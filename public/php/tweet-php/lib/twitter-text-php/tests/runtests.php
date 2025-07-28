<?php
/**
 * Examples for the Twitter Text (PHP Edition) Library.
 *
 * Can be run on command line or in the browser.
 *
 * @author     Mike Cochrane <mikec@mikenz.geek.nz>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Mike Cochrane, Nick Pope
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License v2.0
 */
require_once dirname(__FILE__).'/bootstrap.php';

$browser = (PHP_SAPI != 'cli');

function pretty_format($a)
{
    return preg_replace([
        "/\n/", '/ +\[/', '/ +\)/', '/Array +\(/', '/(?<!\() \[/', '/\[([^]]+)\]/',
        '/"(\d+)"/', '/(?<=^| )\((?= )/', '/(?<= )\)(?=$| )/',
    ], [
        ' ', ' [', ' )', '(', ', [', '"$1"', '$1', '[', ']',
    ], print_r($a, true));
}

$pass_total = 0;
$fail_total = 0;
$pass_group = 0;
$fail_group = 0;

if ($browser) {
    echo <<<'EOHTML'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en-GB" xml:lang="en-GB" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>Twitter Text (PHP Edition) Library » Conformance</title>
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/
body {
  font-family: Arial, sans-serif;
  font-size: 12px;
}
.pass { color: #090; }
.fail { color: #f00; }
/*]]>*/-->
</style>
</head>
<body>
EOHTML;
}

echo $browser ? '<h1>' : "\033[1m";
echo 'Twitter Text (PHP Edition) Library » Conformance';
echo $browser ? '</h1>' : "\033[0m".PHP_EOL.'==============================================='.PHP_EOL;
echo PHP_EOL;

echo $browser ? '<h2>' : "\033[1m";
echo 'Extraction Conformance';
echo $browser ? '</h2>' : "\033[0m".PHP_EOL.'----------------------'.PHP_EOL;
echo PHP_EOL;

// Load the test data.
$data = Spyc::YAMLLoad($DATA.'/extract.yml');

// Define the functions to be tested.
$functions = [
    'hashtags' => 'extractHashtags',
    'urls' => 'extractURLs',
    'mentions' => 'extractMentionedUsernames',
    'replies' => 'extractRepliedUsernames',
    'hashtags_with_indices' => 'extractHashtagsWithIndices',
    'urls_with_indices' => 'extractURLsWithIndices',
    'mentions_with_indices' => 'extractMentionedUsernamesWithIndices',
];

// Perform testing.
foreach ($data['tests'] as $group => $tests) {
    echo $browser ? '<h3>' : "\033[1m";
    echo 'Test Group - '.ucfirst(str_replace('_', ' ', $group));
    echo $browser ? '</h3>' : ":\033[0m".PHP_EOL;
    echo PHP_EOL;

    if (! array_key_exists($group, $functions)) {
        echo $browser ? '<p>' : "   \033[1;35m";
        echo 'Skipping Test...';
        echo $browser ? '</p>' : "\033[0m".PHP_EOL;
        echo PHP_EOL;
        continue;
    }
    $function = $functions[$group];
    $pass_group = 0;
    $fail_group = 0;
    if ($browser) {
        echo '<ul>', PHP_EOL;
    }
    foreach ($tests as $test) {
        echo $browser ? '<li>' : ' - ';
        echo $test['description'], ' ... ';
        $extracted = Twitter_Extractor::create($test['text'])->$function();
        if ($test['expected'] == $extracted) {
            $pass_group++;
            echo $browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m";
        } else {
            $fail_group++;
            echo $browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m";
            if ($browser) {
                echo '<pre>';
                echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
                echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
                echo 'Actual:   '.pretty_format($extracted);
                echo '</pre>';
            } else {
                echo PHP_EOL, PHP_EOL;
                echo '   Original: '.$test['text'], PHP_EOL;
                echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
                echo '   Actual:   '.pretty_format($extracted), PHP_EOL;
            }
        }
        if ($browser) {
            echo '</li>';
        }
        echo PHP_EOL;
    }
    if ($browser) {
        echo '</ul>';
    }
    echo PHP_EOL;
    $pass_total += $pass_group;
    $fail_total += $fail_group;
    echo $browser ? '<p class="group">' : "   \033[1;33m";
    printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
    echo $browser ? '</p>' : "\033[0m".PHP_EOL;
    echo PHP_EOL;
}

echo $browser ? '<h2>' : "\033[1m";
echo 'Autolink Conformance';
echo $browser ? '</h2>' : "\033[0m".PHP_EOL.'--------------------'.PHP_EOL;
echo PHP_EOL;

// Load the test data.
$data = Spyc::YAMLLoad($DATA.'/autolink.yml');

// Define the functions to be tested.
$functions = [
    'usernames' => 'addLinksToUsernamesAndLists',
    'lists' => 'addLinksToUsernamesAndLists',
    'hashtags' => 'addLinksToHashtags',
    'urls' => 'addLinksToURLs',
    'all' => 'addLinks',
];

// Perform testing.
foreach ($data['tests'] as $group => $tests) {
    echo $browser ? '<h3>' : "\033[1m";
    echo 'Test Group - '.ucfirst(str_replace('_', ' ', $group));
    echo $browser ? '</h3>' : ":\033[0m".PHP_EOL;
    echo PHP_EOL;

    if (! array_key_exists($group, $functions)) {
        echo $browser ? '<p>' : "   \033[1;35m";
        echo 'Skipping Test...';
        echo $browser ? '</p>' : "\033[0m".PHP_EOL;
        echo PHP_EOL;
        continue;
    }
    $function = $functions[$group];
    $pass_group = 0;
    $fail_group = 0;
    if ($browser) {
        echo '<ul>', PHP_EOL;
    }
    foreach ($tests as $test) {
        echo $browser ? '<li>' : ' - ';
        echo $test['description'], ' ... ';
        $linked = Twitter_Autolink::create($test['text'], false)
      ->setNoFollow(false)->setExternal(false)->setTarget('')
      ->setUsernameClass('tweet-url username')
      ->setListClass('tweet-url list-slug')
      ->setHashtagClass('tweet-url hashtag')
      ->setURLClass('')
      ->$function();
        // XXX: Need to re-order for hashtag as it is written out differently...
        //      We use the same wrapping function for adding links for all methods.
        if ($group == 'hashtags') {
            $linked = preg_replace([
                '!<a class="([^"]*)" href="([^"]*)">([^<]*)</a>!',
                '!title="＃([^"]+)"!',
            ], [
                '<a href="$2" title="$3" class="$1">$3</a>',
                'title="#$1"',
            ], $linked);
        }
        if ($test['expected'] == $linked) {
            $pass_group++;
            echo $browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m";
        } else {
            $fail_group++;
            echo $browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m";
            if ($browser) {
                echo '<pre>';
                echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
                echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
                echo 'Actual:   '.pretty_format($linked);
                echo '</pre>';
            } else {
                echo PHP_EOL, PHP_EOL;
                echo '   Original: '.$test['text'], PHP_EOL;
                echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
                echo '   Actual:   '.pretty_format($linked), PHP_EOL;
            }
        }
        if ($browser) {
            echo '</li>';
        }
        echo PHP_EOL;
    }
    if ($browser) {
        echo '</ul>';
    }
    echo PHP_EOL;
    $pass_total += $pass_group;
    $fail_total += $fail_group;
    echo $browser ? '<p class="group">' : "   \033[1;33m";
    printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
    echo $browser ? '</p>' : "\033[0m".PHP_EOL;
    echo PHP_EOL;
}

echo $browser ? '<h2>' : "\033[1m";
echo 'Hit Highlighter Conformance';
echo $browser ? '</h2>' : "\033[0m".PHP_EOL.'---------------------------'.PHP_EOL;
echo PHP_EOL;

// Load the test data.
$data = Spyc::YAMLLoad($DATA.'/hit_highlighting.yml');

// Define the functions to be tested.
$functions = [
    'plain_text' => 'addHitHighlighting',
    'with_links' => 'addHitHighlighting',
];

// Perform testing.
foreach ($data['tests'] as $group => $tests) {
    echo $browser ? '<h3>' : "\033[1m";
    echo 'Test Group - '.ucfirst(str_replace('_', ' ', $group));
    echo $browser ? '</h3>' : ":\033[0m".PHP_EOL;
    echo PHP_EOL;

    if (! array_key_exists($group, $functions)) {
        echo $browser ? '<p>' : "   \033[1;35m";
        echo 'Skipping Test...';
        echo $browser ? '</p>' : "\033[0m".PHP_EOL;
        echo PHP_EOL;
        continue;
    }
    $function = $functions[$group];
    $pass_group = 0;
    $fail_group = 0;
    if ($browser) {
        echo '<ul>', PHP_EOL;
    }
    foreach ($tests as $test) {
        echo $browser ? '<li>' : ' - ';
        echo $test['description'], ' ... ';
        $highlighted = Twitter_HitHighlighter::create($test['text'])->$function($test['hits']);
        if ($test['expected'] == $highlighted) {
            $pass_group++;
            echo $browser ? '<span class="pass">PASS</span>' : "\033[1;32mPASS\033[0m";
        } else {
            $fail_group++;
            echo $browser ? '<span class="fail">FAIL</span>' : "\033[1;31mFAIL\033[0m";
            if ($browser) {
                echo '<pre>';
                echo 'Original: '.htmlspecialchars($test['text'], ENT_QUOTES, 'UTF-8', false), PHP_EOL;
                echo 'Expected: '.pretty_format($test['expected']), PHP_EOL;
                echo 'Actual:   '.pretty_format($highlighted);
                echo '</pre>';
            } else {
                echo PHP_EOL, PHP_EOL;
                echo '   Original: '.$test['text'], PHP_EOL;
                echo '   Expected: '.pretty_format($test['expected']), PHP_EOL;
                echo '   Actual:   '.pretty_format($highlighted), PHP_EOL;
            }
        }
        if ($browser) {
            echo '</li>';
        }
        echo PHP_EOL;
    }
    if ($browser) {
        echo '</ul>';
    }
    echo PHP_EOL;
    $pass_total += $pass_group;
    $fail_total += $fail_group;
    echo $browser ? '<p class="group">' : "   \033[1;33m";
    printf('Group Results: %d passes, %d failures', $pass_group, $fail_group);
    echo $browser ? '</p>' : "\033[0m".PHP_EOL;
    echo PHP_EOL;
}

echo $browser ? '<p class="total">' : "   \033[1;36m";
printf('Total Results: %d passes, %d failures', $pass_total, $fail_total);
echo $browser ? '</p>' : "\033[0m".PHP_EOL;
echo PHP_EOL;

if ($browser) {
    echo <<<'EOHTML'
</body>
</html>
EOHTML;
}
