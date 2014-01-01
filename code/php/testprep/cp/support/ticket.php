<?php
/**
 * Shows and lets members view and reply to support tickets
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: ticket.php 1389 2004-11-29 16:39:04Z elijah $
 * @package NiftyCMS
 * @subpackage cp_support
 */
require_once '../../site/inc/Page.class.php';
require_once '../../site/inc/Form.class.php';
require_once '../../site/inc/Support.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$ticket_id  = (int) $_GET['id'];        // Id of the ticket
$member_id  = $Member->getId();   // Get the members id
$Support = new Support($page_type, $settings, $member_id);
$output .= $Support->showTicket($ticket_id, $member_id);
$output .= $Page->showFooter();
echo $output;
?>
