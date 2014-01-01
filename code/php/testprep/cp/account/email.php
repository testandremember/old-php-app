<?php
/**
 * Lets the member change their email address
 *
 * @version $Id: email.php 1317 2004-10-30 18:15:12Z elijah $
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage cp_account
*/
require_once '../../site/inc/Page.class.php';
require_once '../../site/inc/Form.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
// Get the members id
$member_id = $Member->getId();

// Get the members current information
$query  = 'SELECT email, password FROM cp_members WHERE id="'.$member_id.'"';
$result = DB::query($query);
$member = DB::fetchAssoc($result);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    Form::redirectOnCancel($settings['cp_uri'].'/#account');

    // Trim spaces, and convert quotes to html entities
    $posted = Form::sanitizeData($_POST);

    // Create a form object
    $Form = new Form($_POST, $posted, $page_type);

   // Make sure that the user entered their current password correctly
    if ($member['password'] != md5($posted['current_password'])) {
        // Error: You did not enter your current password correctly.
        $Form->setError('current_password', 27);
    }

    //  Make sure that email address is not in use by another member
 //   $query2 = 'SELECT email, password FROM cp_members
 //              WHERE email = "'.$posted['email'].'" AND id != "'.$member_id.'"';
 //   $result2 = DB::query($query2);

       // Make sure that the new email address is not in use by another member
  //  if (0 != DB::numRows($result2)) {
        // Error: An account with that email address already exists.
        // Please use a different email addresss.
  //      $Form->setError('email', 26);
 //   }

    // Validate the form input
    $Form->isEmail('email');
    $Form->areEqual('email', 'email_confirm');
    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {
        // Update the database with the update information
        $query = 'UPDATE cp_members SET email="'.$posted['email'].'"
                  WHERE id="'.$member_id.'"';
        DB::query($query);

        Event::logEvent(29, $member_id, $page_type, '');
        header('Location: '.$settings['cp_uri'].'/?event=29&account=1#account');
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    // Create a form object
    $Form = new Form($_POST, array(), $page_type);
}

// show a form that lets the member change their email address
$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Change Email Address');
$info  = '<b>Update your email address</b> - Fill in the form below to change ';
$info .= 'your email address.<br />';
$info .= 'To protect your account, you must enter your account password.';
$info .= '<br /> Your current email address is: <b>'.$member['email'].'</b>';
$output .= $Form->showInfo($info);
$output .= $Form->showPasswordInput('Password', 'current_password');
$output .= $Form->showTextInput('New Email Address', 'email', 127, 0);
$output .= $Form->showTextInput('Confirm New Email Address', 'email_confirm', 127, 0);
$output .= $Form->showSubmitButton('Update Email Address', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
