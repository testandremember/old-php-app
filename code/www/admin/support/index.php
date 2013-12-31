<?php
/**
 * Shows a list of the current support tickets
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: index.php 1361 2004-11-01 03:19:15Z elijah $
 * @package NiftyCMS
 * @subpackage admin_support
 */
require_once '../../testprep/site/inc/Page.class.php';
require_once '../../testprep/site/inc/Support.class.php';
$page_type = 'admin';
$Member  = new Member($page_type, $settings);
$Page    = new Page($page_type, $settings, $Member);

$output = $Page->showHeader();

if (FALSE != isset($_GET['event'])) {
    $output .= Event::showMessage($_GET['event']);
}

$member_id = $Member->getId();
$Support = new Support($page_type, $settings, $member_id);

$output .= $Support->showTicketList();

$output .= $Page->showFooter();
echo $output;
?>
