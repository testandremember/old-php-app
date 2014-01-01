<?php
/**
 * Activates an Admin Panel account.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: activate-admin-account.php 1423 2004-11-29 22:31:11Z elijah $
 * @package NiftyCMS
 * @subpackage admin_admin-accounts
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$member_id = $Member->getId();
$admin_member_id = (int) $_GET['id'];

// Delete member's page
$query = 'UPDATE admin_members SET account_active="1" WHERE id="'.$admin_member_id.'" LIMIT 1';
DB::query($query);

// Member account activated
$output .= Event::logEvent(88, $member_id, $page_type, '');

DB::close();

$uri = $settings['admin_uri'].'/members/admin/?event=88';
header('Location: '.$uri);
?>