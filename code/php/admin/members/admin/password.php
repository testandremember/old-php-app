<?php
/**
 * Lets the member change their password
 *
 * @version $Id: password.php 1422 2004-11-29 22:29:13Z elijah $
 * @author Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage admin_admin-accounts
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$Form   = new Form($_POST, array());

$output = $Page->showHeader();

if ('POST' == $_SERVER['REQUEST_METHOD']) {

    // If the cancel button was pressed then send the Admin Accounts section
    if (array_key_exists('cancel', $_POST)) {
        header('Location: '.$settings['admin_uri'].'/members/admin/');
        exit;
    }

    // Get the members id
    $member_id = (int) $_GET['id'];
    // $member_id = $Member->getId();

    // Trim spaces, and convert quotes to html entities
    $posted = Form::SanitizeData($_POST);

/*
    // Get the members current password
    $query  = 'SELECT password FROM admin_members WHERE id="'.$member_id.'"';
    $result = DB::query($query);
    $member = DB::fetchAssoc($result);

    // Make sure that the user entered their current password correctly
    if ($member['password'] != md5($posted['current_password'])) {
        // Error: You did not enter your current password correctly.
        $Form->SetError('current_password', 27);
        // $output .= '<div class="error">Error: There were problems with your form input. Please correct the errors below and try again.</div>';
    }
*/

    // Validate the form input
   //  $Form->IsValidPassword('password', $_POST['password']);
    $Form->AreEqual('password', 'password_confirm');

    // If there were not any form validation errors
    if (FALSE == $Form->HasErrors()) {
        // Update the database with the updated password
        $query = 'UPDATE admin_members SET password="'.md5($posted['password']).'" WHERE id="'.$member_id.'"';
        DB::query($query);
        // Send member back to CP index
        header('Location: '.$settings['admin_uri'].'/members/admin/');
        exit;
    } else {
        // Problem changing password
        $output .= Event::showAndLog(8, $member_id, $page_type);
    }
}

// show a form that lets the member update their information

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Change Password');
$info  = '<b>Choose new Password</b> - Fill in the form below to change your ';
$info .= 'account access password.';
$output .= $Form->showInfo($info);
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
