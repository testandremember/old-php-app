<?php
/**
 * Lets an Admin add another Admin Panel account
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-account.php 1697 2005-01-03 19:41:01Z elijah $
 * @package NiftyCMS
 * @subpackage admin_admin-accounts
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Page   = new Page($page_type, $settings, $Member);
$Member = new Member($page_type, $settings);
$output = $Page->showHeader();

// Trim spaces, and convert quotes to html entities
$posted = Form::SanitizeData($_POST);

    $Form = new Form($posted,$posted);

if ('POST' == $_SERVER['REQUEST_METHOD']) {

    $valid_email = $Form->IsEmail('email');
    $Form->AreEqual('email', 'email_confirm', 'Email Addresses');
//    $Form->HasPickedOption('duration_years');
    $Form->IsFilledIn('first_name');
    $Form->IsFilledIn('last_name');
    $Form->IsFilledIn('login_name');
    /*
    $Form->IsFilledIn('address1');
    $Form->IsFilledIn('city');
    $Form->HasPickedOption('state');
    $Form->IsZipCode('zip');
    $Form->IsPhoneNumber('night_phone', FALSE);
    $Form->IsPhoneNumber('day_phone', TRUE);
    */
    $Form->IsValidPassword('password', $_POST['password']);
    $Form->AreEqual('password', 'password_confirm');

    // If there were not any form validation errors
    if (FALSE == $Form->HasErrors()) {
         if (FALSE == empty($posted['support_notify'])) {
            $posted['support_notify'] = 1;
        } else {
            $posted['support_notify'] = 0;
        }
         // Insert the new members information
         $insert = array('id' => '', 'first_name' => $posted['first_name'],
                         'last_name' => $posted['last_name'],
                         'login_name' => $posted['login_name'],
                         'password' => md5($posted['password']),
                         'account_active' => 1,
                         'email' => $posted['email'],
                         'support_notify' => $posted['support_notify']);
         DB::insert('admin_members', $insert);
         $output .= Event::logEvent(58, 0, $page_type, $posted['login_name']);
           header('Location: '.$settings['admin_uri'].'/members/admin/?event=58');
           exit;
    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
}

$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');

$output .= $Form->startFieldset('Name');
$info  = '<b>Add Admin Account</b><br />The "Support Notify" option ';
$info .= 'configures whether or not to notify the Admin Member when new ';
$info .= 'messages are posted to support tickets by customers.';
$output .= $Form->showInfo($info);
$output .= $Form->showTextInput('First Name', 'first_name', 32);
$output .= $Form->showTextInput('Last Name', 'last_name', 64);
$output .= $Form->showCheckbox('Support Notify', 'support_notify', 1);
$info  = '<b>Your Email Address and Password</b> - Your password must be at ';
$info .= 'least <b>6 characters long</b> and is case sensitive. Try to make ';
$info .= 'your password as unique as possible.';
$output .= $Form->showInfo($info);
$output .= $Form->showTextInput('Login Name', 'login_name', 255, 0);
$output .= $Form->showTextInput('Email Address', 'email', 127, 0);
$output .= $Form->showTextInput('Confirm Email Address', 'email_confirm',  127, 0);
$uri = $settings['site_uri'].'/support/password-tips.php';
$show_after = '<br />'.$Form->showNewWindowLink('Password Tips', $uri);
$output .= $Form->showPasswordInput('Password', 'password', $show_after);
$output .= $Form->showPasswordInput('Confirm New Password', 'password_confirm');
$output .= $Form->showSubmitButton('Add Admin Panel Account', FALSE);
$output .= $Form->endFieldset();

$output .= $Form->endForm();

// Output the page to the client
$output .= $Page->showFooter();
echo $output;
?>
