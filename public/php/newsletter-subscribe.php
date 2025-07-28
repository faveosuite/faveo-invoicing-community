<?php
/*
Name: 			Newsletter Subscribe
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version:	6.2.0
*/

include './mailchimp/mailchimp.php';

use DrewM\MailChimp\MailChimp;

// Step 1 - Set the apiKey - How get your Mailchimp API KEY - http://kb.mailchimp.com/article/where-can-i-find-my-api-key
$apiKey = '11111111111111111111111111111111-us4';

// Step 2 - Set the listId - How to get your Mailchimp LIST ID - http://kb.mailchimp.com/article/how-can-i-find-my-list-id
$listId = '1111111111';

if (isset($_POST['email'])) {
    $email = $_POST['email'];
} elseif (isset($_GET['email'])) {
    $email = $_GET['email'];
} else {
    $email = '';
}

$MailChimp = new MailChimp($apiKey);

$result = $MailChimp->post('lists/'.$listId.'/members', [
    'email_address' => $email,
    'merge_fields' => ['FNAME' => '', 'LNAME' => ''], // Step 3 (Optional) - Vars - More Information - http://kb.mailchimp.com/merge-tags/using/getting-started-with-merge-tags
    'status' => 'subscribed',
]);

if ($result['id'] != '') {
    $arrResult = ['response' => 'success'];
} else {
    $arrResult = ['response' => 'error', 'message' => $result['detail']];
}

echo json_encode($arrResult);
