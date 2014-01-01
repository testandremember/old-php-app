<?php
/**
 * Emails the member with instructions on how to reset their password
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: forgot-password.php 1428 2004-11-29 23:09:22Z elijah $
 * @package NiftyCMS
 * @subpackage cp
 */
require_once '../site/inc/Page.class.php';
require_once '../site/inc/Form.class.php';
$page_type = 'special';
$Member = new Member('cp', $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

// Sanitize data (strip spaces convert quotes to html entities)
$posted = Form::SanitizeData($_POST);

// Create form object to output form and check errors
$Form = new Form($posted,$posted);

// If the form was submitted
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    // If the cancel button was pressed then send member to the CP login page
    if (array_key_exists('cancel', $_POST)) {
        header('Location: '.$settings['cp_uri'].'/login.php');
        exit;
    }

    // Make sure that the email address is a valid format
    $Form->IsEmail('email');

    // If there are no form errors
    if (FALSE == $Form->HasErrors()) {
        $send_result = $Member->SendPasswordResetEmail($Form);
        if (FALSE == $send_result) {
            if (FALSE == $Form->HasErrors()) {
                // There was a problem sending the email.
                $output .= Event::showAndLog(50, '', $page_type, $posted['email']);
            } else {
                // You did not enter a valid email address.
                $output .= Event::showAndLog(51, '', $page_type, $posted['email']);
            }
        } else {
            // An email has been sent with instructions on how to reset your password.
            header('Location: '.$settings['cp_uri'].'/login.php?event=52');
            exit;
        }
    }  else {
        // You did not enter a valid email address.
        $output .= Event::showAndLog(51, '', $page_type, $posted['email']);
    }
}

    // Create the form
    $output .= $Form->startForm($_SERVER['PHP_SELF']);
    $output .= $Form->startFieldset('Password Reset Request');
    $info    = '<b>Password Reset</b> - Enter your email address below and ';
    $info   .= 'you will recieve instructions on how to choose a new password.';
    $output .= $Form->showInfo($info);
    $output .= $Form->showTextInput('Email Address', 'email', 127);
    $output .= $Form->showSubmitButton('Send Reset Instructions', TRUE);
    $output .= $Form->endFieldset();
    $output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
