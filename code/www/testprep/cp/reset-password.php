<?php
/**
 * Lets a member reset their password
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: reset-password.php 1429 2004-11-29 23:10:11Z elijah $
 * @package NiftyCMS
 * @subpackage cp
 */
require_once '../site/inc/Page.class.php';
require_once '../site/inc/Form.class.php';
$page_type = 'special';
$Member = new Member('cp', $settings);        // Create Member object to check logins etc
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

// Delete expired password reset keys
$query = 'DELETE FROM cp_password_resets WHERE expires - '.time().' < 1';
DB::query($query);

$key   =    DB::MakeSafe($_GET['key']);
$query =    'SELECT member_id,expires FROM cp_password_resets
             WHERE reset_key = "'.$key.'" LIMIT 1';
$result =   DB::query($query);
$num_rows = DB::numRows($result);
$row =      DB::fetchAssoc($result);

if (1 == $num_rows) {
    $key_valid = 1;
    // $error = '';
} else {
    $key_valid = 0;
    // $output .= '<div class="error">Your password reset key is invalid or has expired. You can resend the password reset email by going to the <a href="'.$settings['cp_uri'].'/forgot-password.php">forgotten password form</a>.</div>';
    $output .= Event::showAndLog(55, '', $page_type, $key);
    $output .= '<p class="error">You can resend the password reset email by going to the <a href="'.$settings['cp_uri'].'/forgot-password.php">forgotten password form</a></p>';
}

// Sanitize data (strip spaces convert quotes to html entities)
$posted = Form::SanitizeData($_POST);

// Create form object to output form and check errors
$Form = new Form($posted,$posted);

// If the form was submitted
if ('POST' == $_SERVER['REQUEST_METHOD'] AND 1 == $key_valid) {

    $Form->IsValidPassword('password', $_POST['password']);
    $Form->AreEqual('password', 'password_confirm');

    // If there are no form errors
    if (FALSE == $Form->HasErrors()) {

        $password = md5($posted['password']);
        // Update the database with the updated password
        $query = "UPDATE cp_members SET password='".$password."' WHERE id='".$row['member_id']."'";
        DB::query($query);

        // Delete the password reset key that was just used

        $query = 'DELETE FROM cp_password_resets WHERE reset_key = "'.$key.'"';
        DB::query($query);

        $output .= Event::logEvent(31, $row['member_id'], $page_type, '');

        $Member->LogMemberIn($row['member_id']);
        header('Location:'.$settings['cp_uri'].'/?event=31');
        //    $update_result = '<div class="success">Your password was successfully updated.</div>';
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, 0, $page_type, '');
    }
}

if (1 == $key_valid) {
    // Create the form
    $output .= $Form->startForm($_SERVER['REQUEST_URI']);
    $output .= $Form->startFieldset('Reset Password Form');
    $output .= $Form->showInfo('<b>Password Reset</b> - Choose a new password by filling in the form below.');
    $uri = $settings['site_uri'].'/support/password-tips.php';
    $show_after = '<br />'.$Form->showNewWindowLink('Password Tips', $uri);
    $output .= $Form->showPasswordInput('New Password', 'password', $show_after);
    $output .= $Form->showPasswordInput('Confirm New Password', 'password_confirm');
    $output .= $Form->showSubmitButton('Reset Password');
    $output .= $Form->endFieldset();
    $output .= $Form->endForm();
}

$output .= $Page->showFooter();
echo $output;
?>