<?php
/**
 * Deletes a page from the database
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: delete-account.php 1423 2004-11-29 22:31:11Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members_admin
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$member_id = $Member->getId();
$delete_id = (int) $_GET['id'];
// Delete admin account from the database
$query = 'DELETE FROM admin_members WHERE id='.$delete_id.' LIMIT 1';
DB::query($query);

// Member account de-activated
$output .= Event::logEvent(90, $member_id, $page_type, '');

DB::close();

$uri = $settings['admin_uri'].'/members/admin/?event=90';
header('Location: '.$uri);
?>