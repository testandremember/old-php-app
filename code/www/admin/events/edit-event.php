<?php
/**
 * Lets the admin edit or delete an event.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: edit-event.php 1292 2004-10-30 14:01:27Z elijah $
 * @package NiftyCMS
 * @subpackage admin_events
*/
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$event_id = (int) $_GET['id'];
$member_id = $Member->getId();
// If the form was submitted
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $redirect_uri = $settings['admin_uri'].'/events/';
    // If delete_page button was pressed then delete the page
    if (FALSE != array_key_exists('delete_event', $_POST)) {
        // Delete event from the database
        DB::delete('event_messages', 'id="'.$event_id.'"');
        DB::close();
        header('Location: '.$redirect_uri.'?event=76');
        exit;
    }

    // Sanitize posted form data
    // $posted = Form::SanitizeData($_POST);
    $posted = DB::addSlashes($_POST);

    // Update the database with the updated event info
    $query = 'UPDATE event_messages SET title="'.$posted['title'].'", message="'.$posted['message'].'" , type="'.$posted['type'].'"WHERE id="'.$_GET['id'].'"';
    DB::query($query);
    // Log that the event was edited
    Event::logEvent(78, $member_id, $page_type, '');
    DB::close();
    header('Location: '.$redirect_uri.'?event=78');
    exit;
}

$query  = 'SELECT id, title, message, type FROM event_messages WHERE id="'.$_GET['id'].'"';
$result = DB::query($query);
$event  = DB::fetchAssoc($result);

// Create a new Form with default values from the database
$Form = new Form(array(), $event, $page_type);

// Create the form
$output .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Edit Event "'.$event['id'].'"');

$event_types = DB::getColumnOptions('event_messages', 'type');
$output .= $Form->showDropDown('Event Type','type', $event_types);

$output .= $Form->showTextInput('Title', 'title', 255);
$output .= $Form->showTextarea('Message','message');
$more_buttons = array(1 =>
                      array('label' => 'Delete Event',
                            'name' => 'delete_event',
                            'description' => 'You can delete this event from the database.',
                            'confirm_message' => 'Are you sure you want to delete this event?'));
$output .= $Form->showSubmitButton('Update Event', TRUE, $more_buttons);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Page->showFooter();
echo $output;
?>
