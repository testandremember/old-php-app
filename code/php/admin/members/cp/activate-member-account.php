<?php
/**
 * Activates a member's account in the database
 *
 * @author: Elijah Lofgren <elijah@truthland.com>
 * @version $Id: activate-member-account.php 1425 2004-11-29 22:33:04Z elijah $
 * @package NiftyCMS
 * @subpackage admin_members
 */
require_once '../../../testprep/site/inc/Page.class.php';
$page_type = 'admin';
$Member = new Member($page_type, $settings);
$Page   = new Page($page_type, $settings, $Member);
$output = $Page->showHeader();

$cp_member_id = (int) $_GET['id'];
$member_id = $Member->getId();

// Dactivate member's account
$query = 'UPDATE cp_members SET account_active="1" WHERE id="'.$cp_member_id.'" LIMIT 1';
DB::query($query);

// Member account activated
$output .= Event::logEvent(88, $member_id, $page_type, '');


DB::close();

$uri = $settings['admin_uri'].'/members/cp/edit-member-account.php?id='.$cp_member_id.'&event=88';
header('Location: '.$uri);
?>