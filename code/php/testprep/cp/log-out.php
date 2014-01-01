<?php
/**
 * Logs out the member
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: log-out.php 1413 2004-11-29 20:09:11Z elijah $
 * @package NiftyCMS
 * @subpackage cp
*/
require_once '../site/inc/Page.class.php';
$page_type = 'cp';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();
$member_id = $Member->getId();
// Log the member out
$Member->logOut();

// Send them to the login page.
header('Location: '.$settings['cp_uri'].'/login.php?event=5');
?>