<?php
/**
 * Shows a login form and authenticates the member.
 *
 * If the user is not logged in it will show a login form. If login data is
 * posted it will check that data against the database and authenticate the
 * member if the login data is correct.
 * @version $Id: login.php 1312 2004-10-30 18:07:18Z elijah $
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @package NiftyCMS
 * @subpackage cp
 */
require_once '../site/inc/Page.class.php';
require_once '../site/inc/Form.class.php';
$page_type = 'special';
$Member = new Member('cp', $settings);        // Create Member object to check logins etc
$Page   = new Page($page_type, $settings, $Member);

// Get the page header
$output = $Page->showHeader();

if (FALSE != $Member->isLoggedIn($reason)) {
    header('Location: '.$settings['cp_uri'].'/');
    exit;
}

// Sanitize posted form data data
$preset_values = Form::SanitizeData($_POST);

// Create form object to output form and check errors
$Form = new Form($preset_values, $preset_values);

// If the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $Form->isFilledIn('login_id');
     // Make sure the submitted login name or customer number contains is only
     // letters and numbers
    $Form->isAlphanumeric('login_id');

    // If there are no form errors
    if (FALSE == $Form->hasErrors()) {
        // Authenticate the member
        $Member->authenticate($Form, $settings['cp_uri']);
    }
}

// show the message if there is one
if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

// Create the form
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Control Panel Log In');
$info  = 'Please login to your Control Panel with your login name or customer ';
$info .= 'number and your account password.';
$output .= $Form->showInfo($info);
$output .= $Form->showTextInput('Login Name or Customer Number', 'login_id', 255, 0);
$uri = $settings['site_uri'].'/support/password-tips.php';
$show_after = '<br />'.$Form->showNewWindowLink('Password Tips', $uri);
$output .= $Form->showPasswordInput('Password', 'password', $show_after);
$output .= $Form->showSubmitButton('Log In');
$output .= $Form->showLink('Forgot your password?', $settings['cp_uri'].'/forgot-password.php');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
