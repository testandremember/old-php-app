<?php
/**
 * Deactivates an Admin Panel account.
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: deactivate-admin-account.php 1423 2004-11-29 22:31:11Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members_admin
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$admin_member_id = (int) $_GET['id'];
$member_id = $Member->getId();

// De-activate member account
$query = 'UPDATE admin_members SET account_active="0" WHERE id="'.$admin_member_id.'" LIMIT 1';
DB::query($query);

// Member account de-activated
$output .= Event::logEvent(89, $member_id, $page_type, '');

DB::close();

$uri = $settings['admin_uri'].'/members/admin/?event=89';
header('Location: '.$uri);
?>