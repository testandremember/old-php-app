<?php
/**
 * Methods used in the support sections of the Member Control Panel and the
 * Admin Panel
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: Support.class.php 1548 2004-12-02 06:41:38Z elijah $
 * @package NiftyCMS
 */

class Support {
    // An array of settings
    var $mSettings = array();
    // The current page type
    var $mPageType;
    // The id of the member viewing the current page
    var $mMemberId;
    // Holds an array of ticket info
    var $mTicket;
    /**
     * Initializes the class
     *
     */
    function Support($pageType, $settings, $memberId)
    {
        $this->mPageType = $pageType;
        $this->mSettings = $settings;
        $this->mMemberId = $memberId;
    }

    /**
     * Shows a list of support tickets
     *
     */
    function showTicketList($memberId = 0)
    {
        $Member = new Member($this->mPageType, $this->mSettings, 0);

        $output  = '<h2>Current Support Tickets</h2>'."\n";
        $output .= '<table class="simple">'."\n";
        $output .= '<thead>'."\n";
        $output .= '<tr>'."\n";
        $output .= '<th>Id</th>'."\n";
        $output .= '<th>Subject</th>'."\n";
        if ('admin' == $this->mPageType) {
        $output .= '<th>Ticket Starter</th>'."\n";
        }
        $output .= '<th>Last Update</th>'."\n";
        $output .= '<th>Last Replier</th>'."\n";
        $output .= '<th>Status</th>'."\n";
        $output .= '</tr>'."\n";
        $output .= '</thead>'."\n";
        $output .= '<tbody>'."\n";
        if (0 != $memberId) {
            $options = 'WHERE member_id="'.$memberId.'" ';
        } else {
            $options = '';
        }
        $options .= 'ORDER BY id DESC';
        $select = array('id', 'subject', 'member_id', 'last_update', 'last_replier',
                        'last_replier_type', 'status');
        $result = DB::select('support_tickets', $select, $options, 0);
        $show_stripe = TRUE;
        while ($ticket = DB::fetchAssoc($result)) {
            if (FALSE == $show_stripe) {
                $output .= '<tr>'."\n";
                $show_stripe = TRUE;
            } else {
                $output .= '<tr class="stripe">'."\n";
                $show_stripe = FALSE;
            }
            $output .= '<td><a href="ticket.php?id='.$ticket['id'].
                '">'.$ticket['id'].'</a></td>'."\n";
            $output .= '<td><a href="ticket.php?id='.$ticket['id'].
                '">'.$ticket['subject'].'</a></td>'."\n";
            if ('admin' == $this->mPageType) {
                $ticket_starter = $Member->getName($ticket['member_id'], 'cp');
                $output .= '<td>'.$ticket_starter.'</td>'."\n";
            }
            $date = date("F j, Y, g:i a", $ticket['last_update']);
            $output .= '<td>'.$date.'</td>'."\n";
            $last_replier = $Member->getName($ticket['last_replier'],
                                           $ticket['last_replier_type']);
            $output .= '<td>'.$last_replier.'</td>'."\n";
            $status = ucfirst($ticket['status']);
            $output .= '<td class="'.$ticket['status'].'">'.$status.'</td>';
            $output .= '</tr>'."\n";
        }
        $output .= '</tbody>'."\n";
        $output .= '</table>'."\n";
        return $output;
    }

    /**
     * Shows a support ticket along with a reply form
     *
     */
    function showTicket($ticketId)
    {
        $post_array = Form::sanitizeData($_POST);
        $this->mForm = new Form($post_array, $post_array);

        $select = array('id', 'subject', 'last_update', 'last_replier',
                        'last_replier_type', 'member_id', 'status',
                        'creation_date');
        $where = 'WHERE id="'.$ticketId.'"';
        if ('cp' == $this->mPageType) {
            $where .= ' AND member_id="'.$this->mMemberId.'"';
        }
        $result = DB::select('support_tickets', $select, $where);
        $num_rows = DB::numRows($result);
        if (1 == $num_rows) {
        $this->mTicket = DB::fetchAssoc($result);

        $output  = $this->updateTicket($ticketId);
        $output .= $this->showTicketOverview($ticketId);
        $output .= $this->showTicketMessages($ticketId);
        $output .= $this->showUpdateForm($ticketId);
        } else {
            $output = Event::showAndLog(85, $this->mMemberId, $this->mPageType);
        }
        return $output;
    }

    /**
     * Updates a ticket if new data is posted to it
     *
     */
    function updateTicket($ticketId)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $redirect_uri = $this->mSettings[$this->mPageType.'_uri'].'/support/';
            Form::redirectOnCancel($redirect_uri);

            if (FALSE != array_key_exists('delete_ticket', $_POST) &&
                'admin' == $this->mPageType) {
                // Delete support ticket from the database
                DB::delete('support_tickets', 'id="'.$ticketId.'"');

                // Delete support ticket messages from the database
                DB::delete('support_messages', 'ticket_id="'.$ticketId.'"', 0);

                DB::close();
                header('Location: '.$redirect_uri.'?event=83');
                exit;
            }

            $this->mForm->isFilledIn('text');

            $posted = Form::sanitizeData($_POST);
            // If there were not any form validation errors
            if (FALSE == $this->mForm->hasErrors()) {
                // Add the new support ticket message to the database
                $insert = array('id' => '', 'ticket_id' => $ticketId,
                                'member_id' => $this->mMemberId,
                                'date' => time(), 'text' => $posted['text'],
                                'member_type' => $this->mPageType);
                DB::insert('support_messages', $insert);
                // Update the support ticket
                $update = array('last_replier' => $this->mMemberId,
                                'last_replier_type' => $this->mPageType);
                if (FALSE != array_key_exists('close_ticket', $_POST)) {
                    $update['status'] = 'closed';
                } else {
                    $update['status'] = 'open';
                }
                $where = 'id ="'.$ticketId.'"';
                DB::update('support_tickets', $update, $where);

                // Refetch ticket info since it was updated
                $this->getTicketInfo($ticketId);

                // Support ticket reply
                $output = Event::showAndLog(72, $this->mMemberId,
                                                         $this->mPageType);
                if ('admin' == $this->mPageType) {
                    // Get member's email address
                    $select = array('email');
                    $where = 'WHERE id="'.$this->mTicket['member_id'].'"';
                    $result = DB::select('cp_members', $select, $where);
                    $member = DB::fetchAssoc($result);
                    $this->sendTicketReplyEmail($member['email'],
                                                $posted['text'], $ticketId,
                                                'cp');
                } else {
                    // Send email to admins who have support_notify set to 1
                    $select = array('email');
                    $where = 'WHERE support_notify=1';
                    $result = DB::select('admin_members', $select, $where, 0);
                    while($member = DB::fetchAssoc($result)) {
                        $this->sendTicketReplyEmail($member['email'],
                                                    $posted['text'], $ticketId,
                                                    'admin');
                    }
                }
                // Make the reply form be empty
                $this->mForm = new Form(array(), array());

            } else {
                // Form Input Error
                $output = Event::showAndLog(71, $this->mMemberId,
                                            $this->mPageType, '');
            }
        } else {
            $output = '';
        }
        return $output;
    }

    /**
     * Gets ticket information out of the database
     *
     */
    function getTicketInfo($ticketId) {
        // Fetch the ticket info
        $select = array('id', 'subject', 'last_update', 'last_replier',
                        'last_replier_type', 'member_id', 'status',
                        'creation_date');
        $where = 'WHERE id="'.$ticketId.'"';
        if ('cp' == $this->mPageType) {
            $where .= ' AND member_id="'.$this->mMemberId.'"';
        }
        $result = DB::select('support_tickets', $select, $where);
        $this->mTicket = DB::fetchAssoc($result);
    }

    /**
     * Shows a form that allows members to reply/close/open support tickets
     *
     */
    function showUpdateForm($ticketId)
    {
        $output = $this->mForm->startForm('ticket.php?id='.$ticketId, 'post');
        $output .= $this->mForm->startFieldset('Update Ticket');
        $output .= $this->mForm->showTextarea('Message', 'text', 10);
        $more_buttons = array();
        if ('open' == $this->mTicket['status']) {
            $more_buttons[] = array('label' => 'Close Ticket',
                                    'name' => 'close_ticket',
                                    'description' => 'If you no longer need help you can close this support ticket.');
            $submit_label = 'Reply';
        } else {
            $submit_label = 'Reopen Ticket';
        }
        if ('admin' == $this->mPageType) {
            $more_buttons[] = array('label' => 'Delete Ticket',
                                    'name' => 'delete_ticket',
                                    'description' => 'You can delete this ticket from the database.',
                                    'confirm_message' => 'Are you sure you want to delete this ticket?');
        }
        $output .= $this->mForm->showSubmitButton($submit_label, TRUE, $more_buttons);


        $output .= $this->mForm->endFieldset();
        $output .= $this->mForm->endForm();
        return $output;
    }


    /**
     * Shows an overview of the ticket
     *
     */
    function showTicketOverview($ticketId)
    {
        $output = '<h2>Ticket Overview</h2>'."\n";
        $tracking_url = $this->mSettings[$this->mPageType.'_uri'].'/support/ticket.php?id='.$ticketId;

        $creation_date = date("F j, Y, g:i a", $this->mTicket['creation_date']);
        $last_update = date("F j, Y, g:i a", $this->mTicket['last_update']);

        $Member = new Member($this->mPageType, $this->mSettings);

        $ticket_creator = $Member->getName($this->mTicket['member_id'],
                                           $this->mTicket['last_replier_type']);
        if ('admin' == $this->mPageType) {
            $ticket_creator = '<a href="'.$this->mSettings['admin_uri'].
                '/members/cp/view-info.php?id='.$this->mTicket['member_id'].'">'.
                $ticket_creator.'</a>';
        }

        $last_replier = $Member->getName($this->mTicket['last_replier'],
                                         $this->mTicket['last_replier_type']);
        $status = ucfirst($this->mTicket['status']);
        $ticket_status = '<span class="'.$this->mTicket['status'].'">'.$status.'</span>';

        $ticket_info = array(
            array('label' => 'Subject', 'value' => $this->mTicket['subject']),
            array('label' => 'Ticket Creator', 'value' => $ticket_creator),
            array('label' => 'Creation Date', 'value' => $creation_date),
            array('label' => 'Ticket Id', 'value' => $this->mTicket['id']),
            array('label' => 'Tracking URL', 'value' => $tracking_url),
            array('label' => 'Last Update', 'value' => $last_update),
            array('label' => 'Last Replier', 'value' => $last_replier),
            array('label' => 'Status', 'value' => $ticket_status)
        );
        $output .= Form::showList($ticket_info);

        return $output;
    }

    /**
     * Shows a support ticket along with it's replies
     *
     */
    function showTicketMessages($ticketId)
    {
        $Member = new Member($this->mPageType, $this->mSettings, 0);
        $output  = '<h2>Ticket Messages</h2>';
        $output .= '<table class="simple">'."\n";
        $output .= '<thead>'."\n";
        $output .= '<tr>'."\n";
        $output .= '<th>Author</th>'."\n";
        $output .= '<th>Contents</th>'."\n";
        $output .= '</tr>'."\n";
        $output .= '</thead>'."\n";
        $output .= '<tbody>'."\n";

        $query = 'SELECT id, text, date, member_id, member_type FROM support_messages
            WHERE ticket_id="'.$ticketId.'" ORDER BY id ASC';
        $result   = DB::query($query);
        $show_stripe = TRUE;
        while ($message = DB::fetchAssoc($result)) {
            if (FALSE == $show_stripe) {
                $output .= '<tr>'."\n";
                $show_stripe = TRUE;
            } else {
                $output .= '<tr class="stripe">'."\n";
                $show_stripe = FALSE;
            }

            $member_name = $Member->getName($message['member_id'], $message['member_type']);
            $output .= '<td><b>'.$member_name.'</b>';
            if ('cp' == $message['member_type']) {
                $output .= '<br /><i>Customer</i>';
            } else {
                $output .= '<br /><i>Staff</i>';
            }
            $output .= '</td>'."\n";
            $text = nl2br($message['text']);
            $output .= '<td>';
            $date = date("F j, Y, g:i a", $message['date']);

            $output .= 'Posted on '.$date;
            $output .= '<hr />';
            $output .= $text.'</td>'."\n";
            $output .= '</tr>'."\n";
        }
        $output .= '</tbody>'."\n";
        $output .= '</table>'."\n";

        return $output;
    }

    /**
     * Sends email when a message is posted to a support ticket
     *
     */
    function sendTicketReplyEmail($emailAddress, $ticketMessage, $ticketId,
                                  $memberType)
    {
        $from     =  $this->mSettings['email_from'];
        $subject  =  '['.$ticketId.']: '.$this->mTicket['subject'];
        $headers  =  "From: $from\r\n";
        $headers .=  "Reply-To: $from\r\n";
        $headers .=  "X-Mailer: PHP/".phpversion()."\r\n";
        $message  = 'Subject: '.$this->mTicket['subject']."\n";
        $message .= "------------------------------------------------------\n\n";
        $message .= $ticketMessage;
        $message .= "\n\n------------------------------------------------------\n";
        $message .= "Ticket Information\n";
        $message .= "------------------------------------------------------\n\n";
        $message .=  'Ticket Id: '.$ticketId."\n";
        $message .=  'Tracking URL: '.$this->mSettings[$memberType.'_uri'].'/support/ticket.php?id='.$ticketId."\n";
        $message .=  'Subject: '.$this->mTicket['subject']."\n";
        $date = date("F j, Y, g:i a", $this->mTicket['creation_date']);
        $message .= 'Created On: '.$date."\n";
        $status = ucfirst($this->mTicket['status']);
        $message .= 'Status: '.$status."\n";
        // Send the email with the new ticket message
        return mail($emailAddress, $subject, $message, $headers );
    }

    /**
     * Sends an email when a member opens a new support ticket.
     *
     */
    function sendTicketOpenEmail($member, $ticket, $ticketId, $creation_date)
    {
        $from     =  $this->mSettings['email_from'];
        $subject  =  '['.$ticketId.']: '.$ticket['subject'];
        $headers  =  "From: $from\r\n";
        $headers .=  "Reply-To: $from\r\n";
        $headers .=  "X-Mailer: PHP/".phpversion()."\r\n";
        $message  = $member['first_name'].','."\n\n";
        $message .= 'Your support ticket has been submitted. We try to reply as soon possible.';
        $message .= "\n\n";
        $message .= 'Please let us know if we can assist you further,'."\n\n";
        $message .= 'The Support Team';
        $message .= "\n\n\n";
        $message .= 'Ticket Information';
        $message .= "\n------------------------------------------------------\n";
        $message .=  'Ticket Id: '.$ticketId."\n";
        $message .=  'Tracking URL: '.$this->mSettings[$this->mPageType.'_uri'].'/support/ticket.php?id='.$ticketId."\n";
        $message .=  'Subject: '.$ticket['subject']."\n";
        $date = date("F j, Y, g:i a", $creation_date);
        $message .= 'Created On: '.$date;
        $message .= "\n------------------------------------------------------\n";
        // Send the email with the new ticket message
        return mail($member['email'], $subject, $message, $headers );
    }
}