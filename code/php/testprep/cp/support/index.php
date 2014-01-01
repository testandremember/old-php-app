<?php
/**
 * Shows a way for member's to submit support tickets and view old ones.
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1549 2004-12-02 06:42:25Z elijah $
 * @package NiftyCMS
 * @subpackage cp_support
 */
require_once '../../site/inc/Page.class.php';
require_once '../../site/inc/Form.class.php';
require_once '../../site/inc/Support.class.php';
$page_type = 'cp';
$Member  = new Member($page_type, $settings);
$Page    = new Page($page_type, $settings, $Member);

$output = $Page->showHeader();
$member_id = $Member->getId();
$Support = new Support($page_type, $settings, $member_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the cancel button was pressed then send the member to the admin index
    Form::redirectOnCancel($settings['cp_uri'].'/#support');

    $posted = Form::SanitizeData($_POST);

    $Form = new Form($posted, $posted);

    $Form->isFilledIn('subject');
    $Form->isFilledIn('text');

    // If there were not any form validation errors
    if (FALSE == $Form->hasErrors()) {
        $creation_date = time();
        // Add the new support ticket to the database
        $insert = array('id' => '', 'member_id' => $member_id,
                        'last_update' => time(), 'creation_date' => $creation_date,
                    'subject' => $posted['subject'], 'status' => 'open',
                    'last_replier' => $member_id, 'last_replier_type' => 'cp');
        DB::insert('support_tickets', $insert);

        // Add the new support ticket message to the database
        $last_insert_id = DB::insertId();
        $insert = array('id' => '', 'ticket_id' => $last_insert_id,
                        'member_id' => $member_id, 'date' => time(),
                        'text' => $posted['text'], 'member_type' => 'cp');
        DB::insert('support_messages', $insert);

        // Log ticket submission event
        Event::logEvent(79, $member_id, $page_type);
        // $output .= Event::showAndLog(79, $member_id, $page_type);

        $select = array('email', 'first_name');
        $where = 'WHERE id="'.$member_id.'"';
        $result = DB::select('cp_members', $select, $where);
        $member = DB::fetchAssoc($result);

        // Email the member and tell them their ticket has been received
        $Support->sendTicketOpenEmail($member, $_POST, $last_insert_id,
                                      $creation_date);

        // Get ticket informatino that will be used by $Support->sendTicketReplyEmail()
        $Support->getTicketInfo($last_insert_id);

        // Send email to admins who have support_notify set to 1
        $select = array('email');
        $where = 'WHERE support_notify=1';
        $result = DB::select('admin_members', $select, $where, 0);
        while($member = DB::fetchAssoc($result)) {
            $Support->sendTicketReplyEmail($member['email'], $posted['text'],
                                        $last_insert_id, 'admin');
        }

        // Redirect the member to the ticket page
        $redirect_uri = $settings['cp_uri'].'/support/ticket.php?id='.$last_insert_id.'&event=79';
        header('Location: '.$redirect_uri);
        exit;

    } else {
        // Problems changing address info
        $output .= Event::showAndLog(71, $member_id, $page_type);
    }
} else {
    $Form = new Form(array(), array());
}

$output .= '<h2>Open New Support Ticket</h2>'."\n";
$output  .= $Form->startForm($_SERVER['REQUEST_URI'], 'post');
$output .= $Form->startFieldset('Open New Support Ticket');
$info  = '<b>Open A Support Ticket</b> - If you are having a problem or need ';
$info .= 'help in any way you can open a support ticket below and we will try ';
$info .= 'to reply as soon as possible.';
$output .= $Form->showInfo($info);
$output .= $Form->showTextInput('Subject', 'subject', 255);
$output .= $Form->showTextarea('Message', 'text', 10);
$output .= $Form->showSubmitButton('Submit New Support Ticket', TRUE);
$output .= $Form->endFieldset();
$output .= $Form->endForm();

$output .= $Support->showTicketList($member_id);

$output .= $Page->showFooter();
echo $output;
?>
