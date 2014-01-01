<?php
/**
 * Lets the admin add an event.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: add-event.php 1293 2004-10-30 14:01:39Z elijah $
 * @package NiftyCMS
 * @subpackage admin_events
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$member_id = $Member->getId();

// If the form was submitted
if ('POST' == $_SERVER['REQUEST_METHOD']) {
        // Sanitize posted form data
    // $posted = Form::SanitizeData($_POST);
    $posted = DB::addSlashes($_POST);

    $query = 'INSERT INTO event_messages (id, title, message, type)
              VALUES ("", "'.$posted['title'].'",
                      "'.$posted['message'].'",
                      "'.$posted['type'].'" )';

    DB::query($query);
    // Log that the event was added to the DB
    Event::logEvent(77, $member_id, $page_type, '');
    DB::close();
    header('Location: '.$settings['admin_uri'].'/events/?event=77');
    exit;
}

// Create form object to output form
$Form = new Form(array(),array());

// Create the form
$output .= $Form->startForm($_SERVER['PHP_SELF'], 'post');
$output .= $Form->startFieldset('Add Event');
$output .= $Form->showInfo('Use the form below to add an event to the database.');

$event_types = DB::getColumnOptions('event_messages', 'type');
$output .= $Form->showDropDown('Event Type','type', $event_types);

$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextarea('Message', 'message');
$output .= $Form->showSubmitButton('Add New Event');
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
