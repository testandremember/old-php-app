<?php
/**
 * Lets the member change their password
 *
 * @version $Id: password.php 1314 2004-10-30 18:13:03Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage cp_account
 */
require_once '../../site/inc/Page.class.php';
require_once '../../site/inc/Form.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$Form   = new Form($_POST, array(), $page_type);

$output = $Page->showHeader();

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    Form::redirectOnCancel($settings['cp_uri'].'/#account');

    // Get the members id
    $member_id = $Member->getId();

    // Get the members current password
    $query  = 'SELECT password FROM cp_members WHERE id="'.$member_id.'"';
    $result = DB::query($query);
    $member = DB::fetchAssoc($result);

    // Trim spaces, and convert quotes to html entities
    $posted = Form::SanitizeData($_POST);

    // Make sure that the user entered their current password correctly
    if ($member['password'] != md5($posted['current_password'])) {
        // Error: You did not enter your current password correctly.
        $Form->setError('current_password', 27);
    }

    // Validate the form input
    $Form->isValidPassword('password', $_POST['password']);
    $Form->areEqual('password', 'password_confirm');

    // If there were not any form validation errors
    if (FALSE == $Form->HasErrors()) {
        // Update the database with the updated password
        $query = 'UPDATE cp_members
                  SET password="'.md5($posted['password']).'"
                  WHERE id="'.$member_id.'"';
        DB::query($query);
        // Send member back to CP index
        header('Location: '.$settings['cp_uri'].'/?event=9&account=1#account');
        exit;
    } else {
        // Problem changing password
        $output .= Event::showAndLog(8, $member_id, $page_type, '');
    }
}

// show a form that lets the member update their information
$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Change Password');
$info  = '<b>Choose new Password</b> - Fill in the form below to change your ';
$info .= 'account password. As a security measure, you will need to confirm ';
$info .= 'your old password. ';
$output .= $Form->showInfo($info);
$output .= $Form->showPasswordInput('Current Password', 'current_password');
$uri = $settings['site_uri'].'/support/password-tips.php';
$show_after = '<br />'.$Form->showNewWindowLink('Password Tips', $uri);
$output .= $Form->showPasswordInput('New Password', 'password', $show_after);
$output .= $Form->showPasswordInput('Confirm New Password', 'password_confirm');
$output .= $Form->showSubmitButton('Update Password', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
