<?php
/**
 * Provides the ablity to edit the account of an admin.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-account.php 1520 2004-12-01 07:09:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members_admin
 */
require_once '../../../testprep/site/inc/Page.class.php';
require_once '../../../testprep/site/inc/Form.class.php';
require_once '../../../testprep/site/inc/Account.class.php';
$page_type = 'admin';
$Account = new Account($settings);
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
 $output = $Page->showHeader(); // Get the page header

$admin_member_id = $_GET['id'];
$member_id = $Member->getId();

// Get the members current information
$select = array('id', 'first_name', 'last_name', 'login_name',
                'support_notify');
$where = 'WHERE id="'.$admin_member_id.'"';
$result = DB::select('admin_members', $select, $where);
$member = DB::fetchAssoc($result);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    Form::redirectOnCancel($settings['admin_uri'].'/members/admin/');

    // Trim spaces, and convert quotes to html entities
    $posted = Form::sanitizeData($_POST);

    // Create a form object
    $Form = new Form($posted, $posted);
    // Validate the form input
    $Form->isFilledIn('first_name');
    $Form->isFilledIn('last_name');
    $valid_login_name = $Form->isAlphanumeric('login_name');
    if (FALSE != $valid_login_name) {
        $AdminMember = new Member('admin', $settings, $admin_member_id);
        $login_name_taken = $AdminMember->isAttributeInUse('login_name',
                                                           $posted['login_name']);

        if (FALSE != $login_name_taken) {
            // That login name is already in use. Please choose a different login name.
            $Form->setError('login_name', 66);
        }
    }

/*
    $Form->IsFilledIn('address1');
    $Form->IsFilledIn('city');
    $Form->HasPickedOption('state');
    $Form->IsZipCode('zip');
    $Form->IsPhoneNumber('night_phone', FALSE);
    $Form->IsPhoneNumber('day_phone', TRUE);
*/
    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {
        if (FALSE == empty($posted['support_ticket_notify'])) {
            $posted['support_ticket_notify'] = 1;
        } else {
            $posted['support_ticket_notify'] = 0;
        }

        // Update the database with the updated information
        $update = array('first_name' => $posted['first_name'],
                        'last_name' => $posted['last_name'],
                        'login_name' => $posted['login_name'],
                        'support_notify' => $posted['support_notify']);

        $where = 'id ="'.$admin_member_id.'"';
        DB::update('admin_members', $update, $where);

        Event::logEvent(80, $member_id, $page_type, '');
        header('Location: '.$settings['admin_uri'].'/members/admin/?event=80');
        exit;

    } else {
        // Form Input Error
        $output .= Event::showAndLog(71, $member_id, $page_type, '');
    }
} else {
    // Create a form object
    $Form = new Form(array(), $member);
    // There was not update so there is no update result
    // $update_result='';
}

// show a form that lets the member update their information
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Admin Account');
$info  = '<b>Edit Admin Account</b><br />The "Support Notify" option ';
$info .= 'configures whether or not to notify the Admin Member when new ';
$info .= 'messages are posted to support tickets by customers.';
$output .= $Form->showInfo($info);
$output .= $Form->showTextInput('Login Name', 'login_name', 255);
$output .= $Form->showTextInput('First Name', 'first_name', 32);
$output .= $Form->showTextInput('Last Name', 'last_name', 64);
$output .= $Form->showCheckbox('Support Notify', 'support_notify', 1);
$output .= $Form->showSubmitButton('Save Information', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
