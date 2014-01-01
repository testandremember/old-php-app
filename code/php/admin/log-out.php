<?php
/**
 * Logs out the Admin Panel member
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: log-out.php 1413 2004-11-29 20:09:11Z elijah $
 * @package NiftyCMS
 * @subpackage admin
*/
require_once '../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$member_id = $Member->getId();
// Log the member out
$Member->logOut();

// Event::logEvent(5, $member_id);
// Send them to the login page.
header('Location: '.$settings[$page_type.'_uri'].'/login.php?event=5');
?>
