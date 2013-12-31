<?php
/**
 * Lets the member update their account information
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: address.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_account
 */
require_once '../../site/inc/Page.class.php';
require_once '../../site/inc/Form.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader(); // Get the page header

$member_id = $Member->getId();

// Get the members current contact information
$query = 'SELECT first_name, last_name, address1, address2, city, state, zip,
                 day_phone, night_phone FROM cp_members
          WHERE id="'.$member_id.'"';
$result = DB::query($query);
$member = DB::fetchAssoc($result);

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    Form::redirectOnCancel($settings['cp_uri'].'/#account');

    // Trim spaces, and convert quotes to html entities
    $posted = Form::sanitizeData($_POST);

    // Create a form object
    $Form = new Form($_POST, $posted, $page_type);
    // Validate the form input
    $Form->isFilledIn('first_name');
    $Form->isFilledIn('last_name');
    $Form->isFilledIn('address1');
    $Form->isFilledIn('city');
    $Form->hasPickedOption('state');
    $Form->isZipCode('zip');
    $Form->isPhoneNumber('night_phone', FALSE);
    $Form->isPhoneNumber('day_phone', TRUE);

    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {
        // Update the database with the update information
        $query = 'UPDATE cp_members
                  SET first_name="'.$posted['first_name'].'",
                      last_name="'.$posted['last_name'].'",
                      address1="'.$posted['address1'].'",
                      address2="'.$posted['address2'].'",
                      city="'.$posted['city'].'",
                      state="'.$posted['state'].'",
                      zip="'.$posted['zip'].'",
                      day_phone="'.$posted['day_phone'].'",
                      night_phone ="'.$posted['night_phone'].'"
                  WHERE id="'.$member_id.'"';
        DB::query($query);

        Event::logEvent(7, $member_id, $page_type, '');
        header('Location: '.$settings['cp_uri'].'/?event=7&account=1#account');
        exit;

    } else {
        // Problems changing address info
        $output .= Event::showAndLog(6, $member_id, $page_type, '');
    }
} else {
    // Create a form object
    $Form = new Form($_POST,$member, $page_type);
}

// show a form that lets the member update their information
$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Edit Your Address Information');
$info  = '<b>Address Information</b> - Use the form below to update your ';
$info .= 'address information';
$output .= $Form->showInfo($info);
$output .= $Form->showTextInput('First Name', 'first_name', 32);
$output .= $Form->showTextInput('Last Name', 'last_name', 64);
$output .= $Form->showTextInput('Address 1', 'address1', 100);
$output .= $Form->showTextInput('Address 2 (optional)', 'address2', 100);
$output .= $Form->showTextInput('City', 'city', 100);
$states = $Form->getStates();
$output .= $Form->showDropDown('State', 'state', $states);
$output .= $Form->showTextInput('Zip Code (5 or 9 digits)', 'zip', 10, 12);
$output .= $Form->showTextInput('Night Phone (000-000-0000)', 'night_phone', 12, 12);
$output .= $Form->showTextInput('Day Phone (optional)', 'day_phone', 12, 12);
$output .= $Form->showSubmitButton('Save Information', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
