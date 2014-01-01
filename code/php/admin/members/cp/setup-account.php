<?php
/**
 * Allows the admin to manually set up and activate a member's account
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: setup-account.php 1411 2004-11-29 20:07:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
require_once '../../../testprep/site/inc/Account.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$query = 'SELECT signup_id, folder_name, num_pages, num_pictures FROM cp_members
          WHERE id="'.$_GET['id'].'" LIMIT 1';
$result = DB::query($query);
$member_data = DB::fetchAssoc($result);

// Trim spaces, and convert quotes to html entities
    $posted = Form::SanitizeData($_POST);

    $Member = new Member($page_type, $settings, $Member);        // Create Member object to check logins etc
// Create a form object
    $Form = new Form($_POST, $posted);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    // If the cancel button was pressed then send the member to the CP index
    if (array_key_exists('cancel', $_POST)) {
        header('Location: '.$settings['admin_uri'].'/members/cp/');
        exit;
    }

    // Validate the form input
    // $Form->IsValidNumber('num_pages', 32000);
    // $Form->IsValidNumber('num_pictures', 32000);

    // If there were not any form validation errors
    // if (FALSE == $Form->HasErrors()) {
        // Update the database with the update information
        // $query='UPDATE cp_members SET account_active="Y" WHERE id="'.$_GET['id'].'"';
        // DB::query($query);
        $Form->isChecked('sure');

        // if (FALSE != isset($posted['sure']) AND FALSE != $posted['sure']) {
        if (FALSE == $Form->HasErrors()) {
            $Account = new Account($settings);
            $Account->setupAccount($member_data['signup_id']);
            header('Location: '.$settings['admin_uri'].'/members/cp/');
            exit;
        } else {
            $output .= '<div class="error">Account was not setup and activated. You must check the box to setup and activate and setup the account</div>';
        }


    // } else {
        // $update_result='<div class="error">There were problems with your form input. Please correct them below.</div>';
    // }
} else {
    // Create a form object
   // $Form = new Form($_POST,$member);
    // There was not update so there is no update result
  //  $update_result='';
}

// show a form that lets the member update their information
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Setup and Activate Member Account');
$info = '<b>NOTE: Accounts should normally be automatically setup when a member ';
$info .= 'pays using PayPal. If accounts are not being set up it may be the sign of a problem.</b><br />';
$info .= 'Are you sure you want to setup and activate the member\'s account?<br />';
$info .= 'This will add '.$member_data['num_pages'].' pages to the database.<br />';
$info .= 'The folder /'.$member_data['folder_name'].'/ will be created.';
$info .= 'The subdomain http://'.$member_data['folder_name'].'.'.$settings['domain'].'/ will be created.';
$output .= $Form->showInfo($info);
$output .= $Form->showCheckbox('Are you sure?', 'sure', 1);

$output .= $Form->showSubmitButton('Setup and Activate Member Account', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();

echo $output;
?>
