<?php
/**
 * Lets an admin change their email address
 *
 * @version $Id: email.php 1420 2004-11-29 22:25:38Z elijah $
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_members_admin
*/
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
// Get the members id
$member_id = (int) $_GET['id'];

// Get the members current information
$query  = 'SELECT email, password FROM admin_members WHERE id="'.$member_id.'"';
$result = DB::query($query);
$member = DB::fetchAssoc($result);

if ('POST' == $_SERVER['REQUEST_METHOD']) {

    // If the cancel button was pressed then send the member to the CP index
    if (array_key_exists('cancel', $_POST)) {
        header('Location: '.$settings['admin_uri'].'/members/admin/');
        exit;
    }

    // Trim spaces, and convert quotes to html entities
    $posted = Form::SanitizeData($_POST);

    // Create a form object
    $Form = new Form($_POST, $posted);
/*
   // Make sure that the user entered their current password correctly
    if ($member['password'] != md5($posted['current_password'])) {
        // Error: You did not enter your current password correctly.
        $Form->SetError('current_password', 27);
        // $output .= '<div class="error">Error: There were problems with your form input. Please correct the errors below and try again.</div>';
    }
*/
    // Validate the form input
    $Form->IsEmail('email');
    $Form->AreEqual('email', 'email_confirm');
    // If there were not any form validation errors
    if (FALSE == $Form->HasErrors()) {
        // Update the database with the update information
        $query = 'UPDATE admin_members SET email="'.$posted['email'].'" WHERE id="'.$member_id.'"';
        DB::query($query);

        Event::logEvent(29, $member_id, $page_type, '');
        header('Location: '.$settings['admin_uri'].'/members/admin/');
        exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    // Create a form object
    $Form = new Form($_POST, array());
    // There was not update so there is no update result
    // $update_result='';
}

// show a form that lets the member update their information
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Change Email Address');
$info  = '<b>Update your email address</b> - Fill in the form below to change ';
$info .= 'your email address. <br />Your current email address is: ';
$info .= '<b>'.$member['email'].'</b>';
$output .= $Form->showInfo($info);
// $output .= $Form->showPasswordInput('Password', 'current_password', 50, 0);
$output .= $Form->showTextInput('New Email Address', 'email', 127, 0);
$output .= $Form->showTextInput('Confirm New Email Address', 'email_confirm', 127, 0);
$output .= $Form->showSubmitButton('Update Email Address', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
