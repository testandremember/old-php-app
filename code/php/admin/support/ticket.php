<?php
/**
 * Shows and lets members reply to support tickets
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: ticket.php 1272 2004-10-30 01:22:18Z elijah $
 * @package NiftyCMS
 * @subpackage cp_page
 */
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Form.class.php';
require_once '../../testprep/site/inc/Support.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$ticket_id = (int) $_GET['id'];        // Id of the ticket
$member_id  = $Member->getId();   // Get the members id
$Support = new Support($page_type, $settings, $member_id);
$output .= $Support->showTicket($ticket_id, $member_id);

$output .= $Page->showFooter();
echo $output;
?>
