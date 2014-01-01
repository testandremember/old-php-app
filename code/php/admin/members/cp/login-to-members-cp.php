<?php
/**
 * Allows the admin to log into a member's Control Panel
 *
 * @author Elijah Lofgren <elijah@truthland.com>
 * @version $Id: login-to-members-cp.php 1411 2004-11-29 20:07:36Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */

require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$CpMember = new Member('cp', $settings);
$CpMember->logMemberIn($_GET['id']);
header('Location:'.$settings['cp_uri'].'/');
?>
